// Admin-side Messenger (admin.html)
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getDatabase, ref, onValue, push, set } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

const firebaseConfig = {
  databaseURL: "https://ace-mtech-default-rtdb.firebaseio.com/"
};
const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

const ticketList = document.getElementById('ticket-list');
const conversationPanel = document.getElementById('conversation-panel');
const conversationTitle = document.getElementById('conversation-title');
const closeConversation = document.getElementById('close-conversation');
const messagesView = document.getElementById('messages-view');
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message-input');

let currentTicketId = null;
let currentTicketSubject = '';
let originalTitle = document.title;
let unreadTotal = 0;
const lastTicketTimestamps = {};
let initialTicketsLoaded = false;

// preferences (persisted)
const pref = { toast: true, sound: false, desktop: false };
function loadPrefs() {
  try { const raw = localStorage.getItem('admin_messenger_prefs'); if (raw) Object.assign(pref, JSON.parse(raw)); } catch(e){}
}
function savePrefs(){ try{ localStorage.setItem('admin_messenger_prefs', JSON.stringify(pref)); }catch(e){} }

function requestDesktopPermission() {
  if (!('Notification' in window)) return Promise.resolve('denied');
  if (Notification.permission === 'default') return Notification.requestPermission();
  return Promise.resolve(Notification.permission);
}

function nativeNotify(title, body) {
  if (!('Notification' in window)) return;
  if (Notification.permission !== 'granted') return;
  try { new Notification(title, { body, tag: 'ace-messenger' }); } catch(e){}
}

function playPing() {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const o = ctx.createOscillator(); const g = ctx.createGain();
    o.type = 'sine'; o.frequency.value = 880;
    g.gain.setValueAtTime(0.0001, ctx.currentTime);
    g.gain.exponentialRampToValueAtTime(0.1, ctx.currentTime + 0.01);
    o.connect(g); g.connect(ctx.destination); o.start();
    setTimeout(()=>{ o.stop(); try{ctx.close();}catch(e){} }, 180);
  } catch(e){}
}

function showToast(text) {
  try {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const t = document.createElement('div');
    t.style.pointerEvents = 'auto';
    t.style.background = '#111';
    t.style.color = '#fff';
    t.style.padding = '10px 14px';
    t.style.borderRadius = '10px';
    t.style.boxShadow = '0 6px 20px rgba(0,0,0,0.2)';
    t.style.fontSize = '13px';
    t.style.opacity = '0';
    t.style.transition = 'opacity 300ms ease';
    t.textContent = text;
    container.appendChild(t);
    // force a reflow then fade in
    // eslint-disable-next-line no-unused-expressions
    t.getBoundingClientRect();
    t.style.opacity = '1';
    const VISIBLE = 6000; // ms visible before fading
    const FADE = 400; // fade duration
    setTimeout(() => { t.style.opacity = '0'; }, VISIBLE);
    setTimeout(() => { if (t.parentNode) t.parentNode.removeChild(t); }, VISIBLE + FADE + 50);
  } catch (e) { /* ignore */ }
}

function bumpUnread(count = 1) {
  unreadTotal += count;
  if (unreadTotal > 0) document.title = `(${unreadTotal}) ${originalTitle}`;
}

function clearUnread() {
  unreadTotal = 0;
  document.title = originalTitle;
}

function formatDate(ts) {
  if (!ts) return '';
  const d = new Date(ts);
  return d.toLocaleString();
}

function loadTickets() {
  ticketList.innerHTML = '';
  onValue(ref(db, 'tickets'), (snapshot) => {
    const data = snapshot.val();
    ticketList.innerHTML = '';
    if (data) {
      Object.keys(data).forEach(ticketId => {
        const ticket = data[ticketId];
        let started = '';
        let lastMsg = '';
        if (ticket.messages) {
          const messagesArr = Object.values(ticket.messages).sort((a, b) => a.timestamp - b.timestamp);
          const firstMsg = messagesArr[0];
          started = firstMsg ? formatDate(firstMsg.timestamp) : '';
          const last = messagesArr.length > 0 ? messagesArr[messagesArr.length - 1] : null;
          lastMsg = last ? last.text : '';
          // notification detection: compare last timestamp with stored
          const lastTs = last ? last.timestamp || 0 : 0;
          if (initialTicketsLoaded) {
            const prev = lastTicketTimestamps[ticketId] || 0;
            if (lastTs > prev) {
              // if new message and it's not from admin, notify
              if (last.sender !== 'admin') {
                // increment per-ticket badge if ticket not open
                const card = ticketList.querySelector(`[data-ticket-id="${ticketId}"]`);
                if (card) {
                  const badge = card.querySelector('.ticket-unread');
                  if (badge && currentTicketId !== ticketId) {
                    let cnt = parseInt(badge.getAttribute('data-count')||'0',10);
                    cnt += 1;
                    badge.setAttribute('data-count', String(cnt));
                    badge.textContent = String(cnt);
                    badge.style.display = 'inline-block';
                    bumpUnread(1);
                  }
                }
                if (pref.toast) showToast(`New message on ${ticket.subject || ticketId}`);
                if (pref.desktop) {
                  requestDesktopPermission().then(p => { if (p === 'granted') nativeNotify('New message', `${ticket.subject || ticketId}: ${String(last.text||'').slice(0,120)}`); });
                }
                if (pref.sound) playPing();
              }
            }
          }
          lastTicketTimestamps[ticketId] = lastTs;
        }
        const div = document.createElement('div');
        div.className = 'ticket-card';
        div.setAttribute('data-ticket-id', ticketId);
        div.innerHTML = `
          <div style="display:flex;align-items:center;justify-content:space-between">
            <div class="ticket-title">${ticket.subject || ticketId}</div>
            <div class="ticket-unread" data-count="0" style="display:none">0</div>
          </div>
          <span class="ticket-status">Inquiry</span>
          <div class="ticket-date">${started ? 'Started ' + started : ''}</div>
          <div style='font-size:11px;color:#444;margin-top:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;'>${lastMsg}</div>
        `;
        div.onclick = () => openTicket(ticketId, ticket.subject);
        ticketList.appendChild(div);
      });
      // mark initial load complete after first render to avoid firing notifications
      if (!initialTicketsLoaded) initialTicketsLoaded = true;
      // Add empty cards for draft look
      for (let i = Object.keys(data).length; i < 3; i++) {
        const empty = document.createElement('div');
        empty.className = 'ticket-card';
        empty.style.background = '#e5e7eb';
        ticketList.appendChild(empty);
      }
    } else {
      ticketList.innerHTML = '<div>No tickets found.</div>';
    }
  });
}

function openTicket(ticketId, subject) {
  currentTicketId = ticketId;
  currentTicketSubject = subject;
  conversationPanel.classList.add('active');
  conversationTitle.textContent = subject || ticketId;
  // mark this ticket as read: clear per-ticket badge and recalc unreadTotal
  const card = ticketList.querySelector(`[data-ticket-id="${ticketId}"]`);
  if (card) {
    const badge = card.querySelector('.ticket-unread');
    if (badge) {
      const cnt = parseInt(badge.getAttribute('data-count')||'0',10);
      if (cnt > 0 && unreadTotal > 0) unreadTotal = Math.max(0, unreadTotal - cnt);
      badge.setAttribute('data-count','0'); badge.style.display = 'none'; badge.textContent = '0';
    }
  }
  // if opening the active ticket, clear the page title badge if no unread
  if (unreadTotal <= 0) clearUnread();
  loadMessages(ticketId);
}

function loadMessages(ticketId) {
  onValue(ref(db, `tickets/${ticketId}/messages`), (snapshot) => {
    const data = snapshot.val();
    console.log('Loaded messages for ticket', ticketId, data); // DEBUG
    let html = '';
    // detect newest timestamp to update lastTicketTimestamps
    let newestTs = lastTicketTimestamps[ticketId] || 0;
    if (data) {
      Object.entries(data).forEach(([msgId, msg]) => {
        if (typeof msg === 'object' && msg.text) {
          if (msg.timestamp && msg.timestamp > newestTs) newestTs = msg.timestamp;
          // User messages left (green border), admin messages right (green fill)
          if (msg.sender === 'admin') {
            html += `
            <div style="width:100%;display:flex;justify-content:flex-end;">
              <div style="display:flex;flex-direction:column;align-items:flex-end;">
                <div class='admin-message-bubble' style="background:#22c55e;color:#fff;border-radius:12px;">${msg.text}</div>
                <div class='admin-message-time' style="text-align:right;margin-top:6px;">Sent ${formatDate(msg.timestamp)}</div>
              </div>
            </div>
            `;
          } else {
            html += `
            <div style="width:100%;display:flex;justify-content:flex-start;">
              <div style="display:flex;flex-direction:column;align-items:flex-start;">
                <div class='admin-message-bubble' style="background:#fff;color:#22c55e;border:2px solid #22c55e;border-radius:12px;">${msg.text}</div>
                <div class='admin-message-time' style="text-align:left;margin-top:6px;">Sent ${formatDate(msg.timestamp)}</div>
              </div>
            </div>
            `;
          }
        }
      });
    } else {
      html = '<div style="text-align:center;color:#888;margin-top:32px;">No messages yet for this ticket.</div>';
    }
    messagesView.innerHTML = html;
    messagesView.scrollTop = messagesView.scrollHeight;
    // update last timestamp for this ticket
    lastTicketTimestamps[ticketId] = newestTs;
  });
}

closeConversation.addEventListener('click', () => {
  conversationPanel.classList.remove('active');
});

messageForm.addEventListener('submit', (e) => {
  e.preventDefault();
  if (!currentTicketId) return;
  const text = messageInput.value.trim();
  if (!text) return;
  const msgRef = push(ref(db, `tickets/${currentTicketId}/messages`));
  set(msgRef, {
    sender: 'admin',
    text,
    timestamp: Date.now()
  });
  messageInput.value = '';
});

loadTickets();

// initialize prefs UI (if present) and load saved prefs
loadPrefs();
const settingsBtn = document.getElementById('settings-btn');
const settingsPanel = document.getElementById('settings-panel');
if (settingsBtn && settingsPanel) {
  const cbToast = document.getElementById('pref-toast');
  const cbSound = document.getElementById('pref-sound');
  const cbDesktop = document.getElementById('pref-desktop');
  if (cbToast) cbToast.checked = !!pref.toast;
  if (cbSound) cbSound.checked = !!pref.sound;
  if (cbDesktop) cbDesktop.checked = !!pref.desktop;
  settingsBtn.addEventListener('click', (e)=>{ settingsPanel.style.display = settingsPanel.style.display === 'block' ? 'none' : 'block'; });
  cbToast && cbToast.addEventListener('change', ()=>{ pref.toast = cbToast.checked; savePrefs(); });
  cbSound && cbSound.addEventListener('change', ()=>{ pref.sound = cbSound.checked; savePrefs(); });
  cbDesktop && cbDesktop.addEventListener('change', ()=>{ pref.desktop = cbDesktop.checked; savePrefs(); if (pref.desktop) requestDesktopPermission(); });
  // close panel when clicking outside
  document.addEventListener('click', (ev)=>{ if (!settingsPanel.contains(ev.target) && !settingsBtn.contains(ev.target)) settingsPanel.style.display='none'; });
}