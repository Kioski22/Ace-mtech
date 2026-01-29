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
          lastMsg = messagesArr.length > 0 ? messagesArr[messagesArr.length - 1].text : '';
        }
        const div = document.createElement('div');
        div.className = 'ticket-card';
        div.innerHTML = `
          <div class="ticket-title">${ticket.subject || ticketId}</div>
          <span class="ticket-status">Inquiry</span>
          <div class="ticket-date">${started ? 'Started ' + started : ''}</div>
          <div style='font-size:13px;color:#444;margin-top:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;'>${lastMsg}</div>
        `;
        div.onclick = () => openTicket(ticketId, ticket.subject);
        ticketList.appendChild(div);
      });
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
  loadMessages(ticketId);
}

function loadMessages(ticketId) {
  onValue(ref(db, `tickets/${ticketId}/messages`), (snapshot) => {
    const data = snapshot.val();
    console.log('Loaded messages for ticket', ticketId, data); // DEBUG
    let html = '';
    if (data) {
      Object.entries(data).forEach(([msgId, msg]) => {
        if (typeof msg === 'object' && msg.text) {
          // User messages left (green border), admin messages right (green fill)
          if (msg.sender === 'admin') {
            html += `
            <div style="width:100%;display:flex;justify-content:flex-end;">
              <div class='admin-message-bubble' style="background:#22c55e;color:#fff;border-radius:12px;">${msg.text}</div>
            </div>
            <div class='admin-message-time' style="text-align:right;">Sent ${formatDate(msg.timestamp)}</div>
            `;
          } else {
            html += `
            <div style="width:100%;display:flex;justify-content:flex-start;">
              <div class='admin-message-bubble' style="background:#fff;color:#22c55e;border:2px solid #22c55e;border-radius:12px;">${msg.text}</div>
            </div>
            <div class='admin-message-time' style="text-align:left;">Sent ${formatDate(msg.timestamp)}</div>
            `;
          }
        }
      });
    } else {
      html = '<div style="text-align:center;color:#888;margin-top:32px;">No messages yet for this ticket.</div>';
    }
    messagesView.innerHTML = html;
    messagesView.scrollTop = messagesView.scrollHeight;
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
