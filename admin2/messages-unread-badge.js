import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getDatabase, onValue, ref } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

const firebaseConfig = {
  databaseURL: "https://ace-mtech-default-rtdb.firebaseio.com/"
};

const BADGE_STYLE_ID = "messages-unread-badge-style";
const LINK_SELECTOR = 'a[href="../admin/messages/admin.html"]';

function ensureBadgeStyles() {
  if (document.getElementById(BADGE_STYLE_ID)) return;

  const style = document.createElement("style");
  style.id = BADGE_STYLE_ID;
  style.textContent = `
    .msgLinkIconWrap {
      position: relative;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-right: 0;
    }
    .msgLinkUnreadBadge {
      position: absolute;
      top: -10px;
      right: -16px;
      min-width: 22px;
      height: 22px;
      padding: 0 6px;
      border-radius: 999px;
      background: #ef4444;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      line-height: 22px;
      text-align: center;
      border: 2px solid #0f172a;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.28);
      display: none;
      z-index: 2;
      pointer-events: none;
    }
  `;
  document.head.appendChild(style);
}

function getOrCreateBadge(link) {
  let badge = link.querySelector(".msgLinkUnreadBadge");
  if (badge) return badge;

  let iconWrap = link.querySelector(".msgLinkIconWrap");
  if (!iconWrap) {
    const icon = link.querySelector("svg");
    if (!icon) return null;

    iconWrap = document.createElement("span");
    iconWrap.className = "msgLinkIconWrap";
    link.insertBefore(iconWrap, icon);
    iconWrap.appendChild(icon);
  }

  badge = document.createElement("span");
  badge.className = "msgLinkUnreadBadge";
  badge.textContent = "0";
  badge.setAttribute("aria-label", "No unread inquiries");
  iconWrap.appendChild(badge);

  return badge;
}

function latestMessageSender(messagesObj) {
  const messages = Object.values(messagesObj || {})
    .filter(Boolean)
    .sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

  if (!messages.length) return null;
  return messages[messages.length - 1].sender || null;
}

function getUnreadTicketCount(ticketsObj) {
  return Object.values(ticketsObj || {}).reduce((count, ticket) => {
    const sender = latestMessageSender(ticket?.messages || {});
    return sender === "user" ? count + 1 : count;
  }, 0);
}

function renderBadgeCount(count) {
  const links = Array.from(document.querySelectorAll(LINK_SELECTOR));
  links.forEach((link) => {
    const badge = getOrCreateBadge(link);
    if (!badge) return;

    if (count > 0) {
      badge.style.display = "inline-block";
      badge.textContent = count > 999 ? "999+" : String(count);
      badge.setAttribute("aria-label", `${count} unread inquiries`);
    } else {
      badge.style.display = "none";
      badge.textContent = "0";
      badge.setAttribute("aria-label", "No unread inquiries");
    }
  });
}

function initUnreadBadge() {
  const links = document.querySelectorAll(LINK_SELECTOR);
  if (!links.length) return;

  ensureBadgeStyles();
  renderBadgeCount(0);

  const app = initializeApp(firebaseConfig, "admin2-unread-badge");
  const db = getDatabase(app);

  onValue(ref(db, "tickets"), (snapshot) => {
    const tickets = snapshot.val() || {};
    renderBadgeCount(getUnreadTicketCount(tickets));
  });
}

initUnreadBadge();
