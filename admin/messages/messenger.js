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

function formatDate(ts) {
  if (!ts) return '';
  const d = new Date(ts);
  return d.toLocaleString();
}

fab.addEventListener('click', () => {
  popup.classList.toggle('active');
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
    if (data) {
      Object.values(data).sort((a, b) => a.timestamp - b.timestamp).forEach(msg => {
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
  });
}
