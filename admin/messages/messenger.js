// Floating Messenger for index.html
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getDatabase, ref, push, set, onValue } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

const firebaseConfig = {
  databaseURL: "https://ace-mtech-default-rtdb.firebaseio.com/"
};
const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

const fab = document.getElementById('messenger-fab');
const popup = document.getElementById('messenger-popup');
const closeBtn = document.getElementById('messenger-close');
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message-input');
const messagesView = document.getElementById('messages-view');
const headerTitle = document.querySelector('#messenger-header span');

let ticketId = null;
let ticketName = null;
let originalTitle = document.title;
let unreadCount = 0;
let lastSeenTs = 0;

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
    // force reflow then fade in
    // eslint-disable-next-line no-unused-expressions
    t.getBoundingClientRect();
    t.style.opacity = '1';
    const VISIBLE = 6000;
    const FADE = 400;
    setTimeout(() => { t.style.opacity = '0'; }, VISIBLE);
    setTimeout(() => { if (t.parentNode) t.parentNode.removeChild(t); }, VISIBLE + FADE + 50);
  } catch (e) {}
}

function bumpTab(count = 1) {
  unreadCount += count;
  document.title = `(${unreadCount}) ${originalTitle}`;
  const fabBadge = document.getElementById('fab-badge');
  if (fabBadge) {
    const prev = parseInt(fabBadge.getAttribute('data-count')||'0',10) || 0;
    const next = prev + count;
    fabBadge.setAttribute('data-count', String(next));
    fabBadge.textContent = String(next);
    fabBadge.style.display = 'inline-flex';
  }
}

function clearTab() {
  unreadCount = 0;
  document.title = originalTitle;
  const fabBadge = document.getElementById('fab-badge');
  if (fabBadge) { fabBadge.setAttribute('data-count','0'); fabBadge.textContent = '0'; fabBadge.style.display = 'none'; }
}

function formatDate(ts) {
  if (!ts) return '';
  const d = new Date(ts);
  return d.toLocaleString();
}

fab.addEventListener('click', () => {
  popup.classList.toggle('active');
  if (popup.classList.contains('active')) {
    // user opened the messenger: clear badge/tab counter
    clearTab();
  }
  if (ticketId) loadMessages();
});
closeBtn.addEventListener('click', () => {
  popup.classList.remove('active');
});

messageForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const text = messageInput.value.trim();
  if (!text) return;
  if (!ticketId) {
    // Create a new ticket for this user session
    ticketName = `Ticket ${Math.floor(100 + Math.random() * 900)}`;
    const ticketRef = push(ref(db, 'tickets'));
    ticketId = ticketRef.key;
    set(ticketRef, {
      subject: ticketName,
      messages: {}
    }).then(() => {
      sendMessage(text, 'user');
      headerTitle.textContent = ticketName;
      loadMessages();
    });
  } else {
    sendMessage(text, 'user');
  }
  messageInput.value = '';
});

function sendMessage(text, sender) {
  const msgRef = push(ref(db, `tickets/${ticketId}/messages`));
  set(msgRef, {
    sender,
    text,
    timestamp: Date.now()
  });
}

function loadMessages() {
  if (!ticketId) return;
  onValue(ref(db, `tickets/${ticketId}/messages`), (snapshot) => {
    const data = snapshot.val();
    let html = '';
    // detect newest ts
    let newest = lastSeenTs;
    if (data) {
      Object.values(data).sort((a, b) => a.timestamp - b.timestamp).forEach(msg => {
        if (msg.timestamp && msg.timestamp > newest) newest = msg.timestamp;
        // User messages right, admin messages left
        if (msg.sender === 'user') {
          html += `
          <div style="width:100%;display:flex;justify-content:flex-end;margin-bottom:18px;">
            <div style="display:flex;flex-direction:column;align-items:flex-end;">
              <div style="background:#22c55e;color:#fff;padding:8px 18px;border-radius:12px;min-width:60px;max-width:220px;word-break:break-word;box-shadow:0 1px 4px rgba(0,0,0,0.08);font-size:16px;font-weight:500;text-align:center;margin-right:0;">
                ${msg.text}
              </div>
              <div style="font-size:12px;color:#888;margin-top:4px;text-align:center;">Sent ${formatDate(msg.timestamp)}</div>
            </div>
          </div>`;
        } else {
          html += `
          <div style="width:100%;display:flex;justify-content:flex-start;margin-bottom:18px;">
            <div style="display:flex;flex-direction:column;align-items:flex-start;">
              <div style="background:#fff;color:#22c55e;padding:8px 18px;border-radius:12px;min-width:60px;max-width:220px;word-break:break-word;box-shadow:0 1px 4px rgba(0,0,0,0.08);font-size:16px;font-weight:500;text-align:center;margin-left:0;border:2px solid #22c55e;">
                ${msg.text}
              </div>
              <div style="font-size:12px;color:#888;margin-top:4px;text-align:center;">Sent ${formatDate(msg.timestamp)}</div>
            </div>
          </div>`;
        }
      });
    }
    messagesView.innerHTML = html;
    messagesView.scrollTop = messagesView.scrollHeight;
    // if there is a new message from admin and the popup is closed, notify user
    if (newest > lastSeenTs) {
      // find last message object
      const msgs = Object.values(data || {}).sort((a,b)=>a.timestamp-b.timestamp);
      const last = msgs[msgs.length-1];
      if (last && last.sender === 'admin') {
        if (!popup.classList.contains('active') || document.hidden) {
          showToast('Admin replied: ' + (last.text || '').slice(0,80));
          bumpTab();
        }
      }
      lastSeenTs = newest;
    }
  });
}
