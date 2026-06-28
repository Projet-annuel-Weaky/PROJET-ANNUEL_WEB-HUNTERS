const chatContainer = document.querySelector(".chat-container");
const messagesEl = document.getElementById("messages");
const inputEl = document.getElementById("input");
const currentUserId = chatContainer
  ? Number(chatContainer.dataset.currentUserId || 0)
  : 0;

let lastMessageId = 0;
let refreshTimer = null;

function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text ?? "";
  return div.innerHTML;
}

function formatDate(value) {
  const date = new Date(value.replace(" ", "T"));
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString("fr-FR");
}

function appendMessage(message) {
  if (!messagesEl || !message) return;

  const wrapper = document.createElement("div");
  wrapper.className =
    Number(message.id_user) === currentUserId ? "message message-own" : "message";
  wrapper.innerHTML = `
    <div class="message-meta">
      <strong>${escapeHtml(message.username)}</strong>
      <span>${escapeHtml(formatDate(message.created_at))}</span>
    </div>
    <div class="message-content">${escapeHtml(message.message)}</div>
  `;
  messagesEl.appendChild(wrapper);
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

async function fetchMessages() {
  if (!messagesEl) return;

  try {
    const response = await fetch(`chat_messages.php?last_id=${lastMessageId}`);
    if (!response.ok) return;

    const data = await response.json();
    const messages = data.messages || [];

    messages.forEach((message) => {
      appendMessage(message);
      lastMessageId = Math.max(lastMessageId, Number(message.id_message) || 0);
    });
  } catch (error) {
    console.error("Impossible de charger les messages", error);
  }
}

async function sendMessage() {
  if (!inputEl) return;
  const message = inputEl.value.trim();
  if (message === "") return;

  try {
    const response = await fetch("chat_messages.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ message }),
    });

    const data = await response.json();
    if (!response.ok || !data.message) return;

    inputEl.value = "";
    appendMessage(data.message);
    lastMessageId = Math.max(lastMessageId, Number(data.message.id_message) || 0);
  } catch (error) {
    console.error("Impossible d'envoyer le message", error);
  }
}

if (inputEl) {
  inputEl.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      event.preventDefault();
      sendMessage();
    }
  });
}

if (messagesEl) {
  fetchMessages();
  refreshTimer = setInterval(fetchMessages, 3000);
}

window.sendMessage = sendMessage;
window.addEventListener("beforeunload", () => {
  if (refreshTimer) clearInterval(refreshTimer);
});
