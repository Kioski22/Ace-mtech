// Admin Messenger (Dashboard layout)
// Compatible with existing backend structure:
// tickets/{ticketId} => { subject, messages }
// tickets/{ticketId}/messages/{messageId} => { sender, text, timestamp, category? }

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import {
  getDatabase,
  ref,
  push,
  set,
  onValue
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

const firebaseConfig = {
  databaseURL: "https://ace-mtech-eee96-default-rtdb.firebaseio.com/"
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

// ===== DOM hooks (must exist in your HTML) =====
const ticketListEl = document.getElementById("ticket-list");
const messagesViewEl = document.getElementById("messages-view");
const messageFormEl = document.getElementById("message-form");
const messageInputEl = document.getElementById("message-input");
const conversationStartDateEl = document.getElementById("conversation-start-date");
const categoryIndicatorEl = document.getElementById("category-indicator");
const categoryTextEl = document.getElementById("category-text");
const filesTriggerEl = document.getElementById("files-trigger");
const filesInputEl = document.getElementById("files-input");
const messagesUnreadBadgeEl = document.getElementById("messages-unread-badge");

// Optional controls
const settingsBtn = document.getElementById("settings-btn");
const logoutBtn = document.getElementById("logout-btn");

// ===== state =====
let selectedTicketId = null;
let selectedTicketUnsubscribe = null;
let allTickets = {};
let pendingFiles = [];

// ---------- helpers ----------
function escapeHtml(str) {
  return String(str || "").replace(/[&<>"']/g, (c) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    "\"": "&quot;",
    "'": "&#39;"
  }[c]));
}

function toDateTimeText(ts) {
  if (!ts) return "";
  const d = new Date(ts);
  return d.toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "numeric",
    minute: "2-digit"
  });
}

function toTimeText(ts) {
  if (!ts) return "";
  const d = new Date(ts);
  return d.toLocaleTimeString("en-US", {
    hour: "numeric",
    minute: "2-digit"
  }).replace(" ", "");
}

function detectCategory(text) {
  const t = (text || "").toLowerCase();

  if (/\b(service|services|offer|offering)\b/.test(t)) return "Services";
  if (/\b(product|products|item|items)\b/.test(t)) return "Products";
  if (/\b(register|registration|sign up|signup|create account)\b/.test(t)) return "Registration";
  if (/\b(price|pricing|cost|fee|payment|plan|plans)\b/.test(t)) return "Pricing";
  if (/\b(error|bug|issue|problem|not working|cannot|can't|failed|fail)\b/.test(t)) return "Technical";
  return "General Inquiry";
}

function statusFromMessages(messagesObj) {
  // Rule: if admin has replied => active; else closed
  // (adjust if your backend has explicit ticket status later)
  const msgs = Object.values(messagesObj || {});
  const hasAdmin = msgs.some(m => m && m.sender === "admin");
  return hasAdmin ? "active" : "closed";
}

function firstMessageTimestamp(messagesObj) {
  const msgs = Object.values(messagesObj || {}).filter(Boolean);
  if (!msgs.length) return Date.now();
  return msgs.reduce((min, m) => Math.min(min, m.timestamp || Date.now()), msgs[0].timestamp || Date.now());
}

function latestUserCategory(messagesObj) {
  const msgs = Object.values(messagesObj || {})
    .filter(Boolean)
    .sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

  for (let i = msgs.length - 1; i >= 0; i--) {
    if (msgs[i].sender === "user") {
      return msgs[i].category || detectCategory(msgs[i].text || "");
    }
  }
  return "General Inquiry";
}

function latestMessageSender(messagesObj) {
  const msgs = Object.values(messagesObj || {})
    .filter(Boolean)
    .sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

  if (!msgs.length) return null;
  return msgs[msgs.length - 1].sender || null;
}

function getUnreadTicketCount(ticketsObj) {
  return Object.values(ticketsObj || {}).reduce((count, ticket) => {
    const sender = latestMessageSender(ticket?.messages || {});
    return sender === "user" ? count + 1 : count;
  }, 0);
}

function renderUnreadBadge(unreadCount) {
  if (!messagesUnreadBadgeEl) return;

  if (unreadCount > 0) {
    messagesUnreadBadgeEl.style.display = "inline-block";
    messagesUnreadBadgeEl.textContent = unreadCount > 999 ? "999+" : String(unreadCount);
    messagesUnreadBadgeEl.setAttribute("aria-label", `${unreadCount} unread inquiries`);
    return;
  }

  messagesUnreadBadgeEl.style.display = "none";
  messagesUnreadBadgeEl.textContent = "0";
  messagesUnreadBadgeEl.setAttribute("aria-label", "No unread inquiries");
}

// ---------- UI render ----------
function renderTicketList(ticketsObj) {
  if (!ticketListEl) return;

  const entries = Object.entries(ticketsObj || {}).map(([id, t]) => ({ id, ...t }));
  entries.sort((a, b) => {
    const aTs = Math.max(...Object.values(a.messages || {}).map(m => m?.timestamp || 0), 0);
    const bTs = Math.max(...Object.values(b.messages || {}).map(m => m?.timestamp || 0), 0);
    return bTs - aTs;
  });

  if (!entries.length) {
    ticketListEl.innerHTML = `<div style="padding:12px;color:#666;font-size:13px;">No inquiries yet.</div>`;
    return;
  }

  ticketListEl.innerHTML = entries.map((ticket) => {
    const subject = ticket.subject || `Ticket ${ticket.id.slice(0, 6)}`;
    const createdTs = firstMessageTimestamp(ticket.messages || {});
    const dateText = toDateTimeText(createdTs);
    const status = statusFromMessages(ticket.messages || {});
    const selectedClass = ticket.id === selectedTicketId ? "selected" : "";

    return `
      <button class="ticket-card ${selectedClass}" data-ticket-id="${ticket.id}" type="button">
        <div class="ticket-avatar">👤</div>
        <div class="ticket-main">
          <div class="ticket-title">${escapeHtml(subject)}</div>
          <div class="ticket-date">${escapeHtml(dateText)}</div>
        </div>
        <div class="ticket-status ${status}">${status}</div>
      </button>
    `;
  }).join("");

  ticketListEl.querySelectorAll(".ticket-card").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.getAttribute("data-ticket-id");
      if (!id) return;
      selectTicket(id);
    });
  });
}

function renderMessages(ticketId, ticket, messagesObj) {
  if (!messagesViewEl) return;

  const msgs = Object.entries(messagesObj || {})
    .map(([id, m]) => ({ id, ...m }))
    .sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

  if (!msgs.length) {
    messagesViewEl.innerHTML = `<div style="color:#666;font-size:14px;">No messages yet.</div>`;
    return;
  }

  const firstTs = msgs[0]?.timestamp || Date.now();
  if (conversationStartDateEl) {
    conversationStartDateEl.textContent = toDateTimeText(firstTs);
  }

  const currentCategory = latestUserCategory(messagesObj);
  if (categoryIndicatorEl && categoryTextEl) {
    categoryIndicatorEl.style.display = "inline-block";
    categoryTextEl.textContent = currentCategory;
  }

  let html = "";
  msgs.forEach((msg, index) => {
    const senderIsUser = msg.sender === "user";
    const senderLabel = senderIsUser
      ? (ticket?.subject || "Ticket")
      : "Admin Support (Me)";

    html += `
      <article class="message-block">
        <div class="message-row">
          <div class="msg-avatar">👤</div>
          <div>
            <h3 class="msg-sender">${escapeHtml(senderLabel)}</h3>
            <p class="msg-text">${escapeHtml(msg.text || "")}</p>
            <div class="msg-time">SENT ${escapeHtml(toTimeText(msg.timestamp))}</div>
          </div>
        </div>
      </article>
    `;

    if (index === 0 && msgs.length > 1) {
      html += `<hr class="msg-divider" />`;
    }
  });

  messagesViewEl.innerHTML = html;
  messagesViewEl.scrollTop = messagesViewEl.scrollHeight;
}

// ---------- data wiring ----------
function selectTicket(ticketId) {
  selectedTicketId = ticketId;
  renderTicketList(allTickets);

  if (selectedTicketUnsubscribe) {
    selectedTicketUnsubscribe();
    selectedTicketUnsubscribe = null;
  }

  selectedTicketUnsubscribe = onValue(ref(db, `tickets/${ticketId}`), (snapshot) => {
    const ticket = snapshot.val();
    if (!ticket) {
      messagesViewEl.innerHTML = `<div style="padding:12px;color:#b91c1c;">Ticket no longer exists.</div>`;
      return;
    }
    renderMessages(ticketId, ticket, ticket.messages || {});
  });
}

function bootTickets() {
  onValue(ref(db, "tickets"), (snapshot) => {
    allTickets = snapshot.val() || {};
    renderUnreadBadge(getUnreadTicketCount(allTickets));
    renderTicketList(allTickets);

    if (!selectedTicketId) {
      const firstId = Object.keys(allTickets)[0];
      if (firstId) selectTicket(firstId);
    } else if (!allTickets[selectedTicketId]) {
      selectedTicketId = null;
      const firstId = Object.keys(allTickets)[0];
      if (firstId) selectTicket(firstId);
    }
  });
}

// ---------- send/admin reply ----------
function sendAdminMessage(text) {
  if (!selectedTicketId) return Promise.resolve();

  const inferredCategory = detectCategory(text);
  const msgRef = push(ref(db, `tickets/${selectedTicketId}/messages`));

  // Keep backend-compatible fields + category
  const payload = {
    sender: "admin",
    text,
    category: inferredCategory,
    timestamp: Date.now()
  };

  return set(msgRef, payload);
}

// ---------- events ----------
if (messageFormEl) {
  messageFormEl.addEventListener("submit", async (e) => {
    e.preventDefault();
    const text = (messageInputEl?.value || "").trim();

    // For now, require text. (File upload can be wired once backend endpoint is given)
    if (!text && pendingFiles.length === 0) return;
    if (!selectedTicketId) return;

    try {
      if (text) {
        await sendAdminMessage(text);
      }
      messageInputEl.value = "";
      pendingFiles = [];
      if (filesInputEl) filesInputEl.value = "";
    } catch (err) {
      console.error("Failed to send admin message:", err);
      alert("Failed to send message. Please try again.");
    }
  });
}

if (filesTriggerEl && filesInputEl) {
  filesTriggerEl.addEventListener("click", () => filesInputEl.click());
  filesInputEl.addEventListener("change", () => {
    pendingFiles = Array.from(filesInputEl.files || []);
    // Placeholder only. No upload endpoint provided yet.
    // Keeps hook ready without breaking current backend.
  });
}

if (settingsBtn) {
  settingsBtn.addEventListener("click", () => {
    alert("Settings panel can be wired here.");
  });
}

if (logoutBtn) {
  logoutBtn.addEventListener("click", () => {
    // Replace with real auth logout if available
    alert("Logout hook clicked.");
  });
}

// init
bootTickets();