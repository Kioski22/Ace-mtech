// Floating Messenger for index.html
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getDatabase, ref, push, set, onValue } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

const firebaseConfig = {
  databaseURL: "https://ace-mtech-eee96-default-rtdb.firebaseio.com/"
};
const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

const fab = document.getElementById('messenger-fab');
const popup = document.getElementById('messenger-popup');
const closeBtn = document.getElementById('messenger-close');
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message-input');
const sendButton = document.getElementById('send-message');
const messagesView = document.getElementById('messages-view');
const headerTitle = document.querySelector('#messenger-header .messenger-title')
  || document.querySelector('#messenger-header span');
const headerSubtitle = document.getElementById('messenger-subtitle');
const supportStart = document.getElementById('support-start');
const supportChat = document.getElementById('support-chat');
const supportStartForm = document.getElementById('support-start-form');
const supportName = document.getElementById('support-name');
const supportEmail = document.getElementById('support-email');
const supportSubject = document.getElementById('support-subject');
const supportMessage = document.getElementById('support-message');

let ticketId = null;
let ticketName = null;
let ticketMeta = { name: '', email: '' };
let originalTitle = document.title;
let unreadCount = 0;
let lastSeenTs = 0;
const originalSubtitle = headerSubtitle ? headerSubtitle.textContent : '';

const STORAGE_KEY = 'ace_mtech_ticket';

function escapeHtml(str) {
  return String(str || '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  }[c]));
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

function getTicketLabel(id) {
  if (!id) return 'Send a message';
  const shortId = String(id).slice(-6).toUpperCase();
  return `Ticket #${shortId}`;
}

function applyTicketHeader() {
  if (headerTitle) {
    headerTitle.textContent = getTicketLabel(ticketId);
  }
  if (headerSubtitle) {
    if (ticketName) {
      headerSubtitle.textContent = `Subject: ${ticketName}`;
    } else {
      headerSubtitle.textContent = originalSubtitle || 'We typically reply within the day.';
    }
  }
}

function setChatEnabled(enabled) {
  if (messageInput) messageInput.disabled = !enabled;
  if (sendButton) sendButton.disabled = !enabled;
  if (supportChat) supportChat.style.display = enabled ? 'block' : 'none';
  if (supportStart) supportStart.style.display = enabled ? 'none' : 'block';
}

function persistTicket() {
  if (!ticketId) return;
  const payload = {
    ticketId,
    subject: ticketName || '',
    name: ticketMeta.name || '',
    email: ticketMeta.email || ''
  };
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
  } catch (e) {}
}

function restoreTicket() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return false;
    const saved = JSON.parse(raw);
    if (!saved || !saved.ticketId) return false;
    ticketId = saved.ticketId;
    ticketName = saved.subject || 'Support Ticket';
    ticketMeta = { name: saved.name || '', email: saved.email || '' };
    applyTicketHeader();
    setChatEnabled(true);
    loadMessages();
    return true;
  } catch (e) {
    return false;
  }
}

function clearTicket() {
  ticketId = null;
  ticketName = null;
  ticketMeta = { name: '', email: '' };
  lastSeenTs = 0;
  try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
}

if (fab && popup) {
  fab.addEventListener('click', () => {
    popup.classList.toggle('active');
    if (popup.classList.contains('active')) {
      // user opened the messenger: clear badge/tab counter
      clearTab();
    }
    if (ticketId) loadMessages();
  });
}

if (closeBtn && popup) {
  closeBtn.addEventListener('click', () => {
    popup.classList.remove('active');
  });
}

if (messageForm && messageInput) {
  messageForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const text = messageInput.value.trim();
    if (!text) return;

    if (!ticketId) {
      // Fallback flow when the start form is not present
      ticketName = 'General inquiry';
      const ticketRef = push(ref(db, 'tickets'));
      ticketId = ticketRef.key;
      const payload = {
        subject: ticketName,
        createdAt: Date.now(),
        name: ticketMeta.name || '',
        email: ticketMeta.email || '',
        messages: {}
      };
      set(ticketRef, payload).then(() => {
        sendMessage(text, 'user');
        applyTicketHeader();
        setChatEnabled(true);
        persistTicket();
        loadMessages();
      });
    } else {
      sendMessage(text, 'user');
    }
    messageInput.value = '';
  });
}

if (supportStartForm) {
  supportStartForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = supportName?.value.trim() || '';
    const email = supportEmail?.value.trim() || '';
    const subject = supportSubject?.value.trim() || 'Project inquiry';
    const text = supportMessage?.value.trim() || '';

    if (!name || !email || !subject || !text) return;

    ticketName = subject;
    ticketMeta = { name, email };

    const ticketRef = push(ref(db, 'tickets'));
    ticketId = ticketRef.key;
    const payload = {
      subject: ticketName,
      createdAt: Date.now(),
      name,
      email,
      messages: {}
    };

    set(ticketRef, payload).then(() => {
      sendMessage(text, 'user');
      applyTicketHeader();
      setChatEnabled(true);
      persistTicket();
      loadMessages();
      supportStartForm.reset();
    });
  });
}

function sendMessage(text, sender) {
  const msgRef = push(ref(db, `tickets/${ticketId}/messages`));
  set(msgRef, {
    sender,
    text,
    timestamp: Date.now()
  });
}

function loadMessages() {
  if (!ticketId || !messagesView) return;
  onValue(ref(db, `tickets/${ticketId}/messages`), (snapshot) => {
    const data = snapshot.val();
    let html = '';
    // detect newest ts
    let newest = lastSeenTs;
    if (data) {
      Object.values(data).sort((a, b) => a.timestamp - b.timestamp).forEach(msg => {
        if (msg.timestamp && msg.timestamp > newest) newest = msg.timestamp;
        const rowClass = msg.sender === 'user' ? 'message-row message-row--user' : 'message-row message-row--admin';
        const bubbleClass = msg.sender === 'user' ? 'message-bubble user' : 'message-bubble admin';
        html += `
          <div class="${rowClass}">
            <div class="message-content">
              <div class="${bubbleClass}">${escapeHtml(msg.text)}</div>
              <div class="message-time">Sent ${formatDate(msg.timestamp)}</div>
            </div>
          </div>`;
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

// boot
if (!restoreTicket()) {
  setChatEnabled(true);
  applyTicketHeader();
}