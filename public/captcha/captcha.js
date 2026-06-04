const container = document.getElementById("parts");
const status = document.getElementById("status");
const resetBtn = document.getElementById("resetBtn");
const validateBtn = document.getElementById("validateBtn");

const img = new Image();
let currentImageId = null;
window.captchaSolved = false;

function setCaptchaSolvedState(isSolved) {
  window.captchaSolved = isSolved;

  if (isSolved) {
    container.querySelectorAll("canvas").forEach((canvas) => {
      canvas.draggable = false;
    });
  }
}

async function sendCaptchaStat(action) {
  if (!currentImageId) return;

  try {
    await fetch("captcha/update_captcha_stats.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({ id: currentImageId, action }),
    });
  } catch (error) {
    console.error("Unable to update captcha stats", error);
  }
}

function loadCaptchaImage() {
  return fetch("captcha/fetch_image.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Unable to load captcha image");
      }
      return response.json();
    })
    .then((data) => {
      if (!data.imageData || !data.mimeType || !data.id) {
        throw new Error("Invalid captcha image response");
      }

      currentImageId = data.id;

      return new Promise((resolve, reject) => {
        img.onload = () => resolve();
        img.onerror = () => reject(new Error("Unable to load captcha image."));
        img.src = `data:${data.mimeType};base64,${data.imageData}`;
      });
    });
}

const pieces = [
  { id: 1, x: 0, y: 0 },
  { id: 2, x: 1, y: 0 },
  { id: 3, x: 0, y: 1 },
  { id: 4, x: 1, y: 1 },
];

let currentOrder = [];
let pieceWidth = 0;
let pieceHeight = 0;

function shuffle(array) {
  let shuffled = array.slice();
  for (let i = shuffled.length - 1; i > 0; i--) {
    let j = Math.floor(Math.random() * (i + 1));
    let temp = shuffled[i];
    shuffled[i] = shuffled[j];
    shuffled[j] = temp;
  }
  return shuffled;
}

function updateStatus(state) {
  status.classList.remove("success-message", "fail-message");

  if (state === "nothing") {
    status.textContent = "";
  } else if (state === "base") {
    status.textContent = "Arrange the pieces and click Validate.";
  } else if (state === "complete") {
    status.textContent = "Captcha complete";
    status.classList.add("success-message");
  } else if (state === "error") {
    status.textContent = "Captcha invalid. Please retry.";
    status.classList.add("fail-message");
  }
}

async function validateCaptcha() {
  const order = currentOrder.map((piece) => piece.id);

  try {
    const response = await fetch("captcha/validate.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        image_id: currentImageId,
        order: order,
      }),
    });

    const data = await response.json();

    if (response.ok && data.success) {
      updateStatus("complete");
      setCaptchaSolvedState(true);
      sendCaptchaStat("completed");

      if (data.token) {
        // inject token into all forms so server can verify single-use token on submit
        document.querySelectorAll('form').forEach((form) => {
          let existing = form.querySelector('input[name="captcha_token"]');
          if (!existing) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'captcha_token';
            inp.value = data.token;
            form.appendChild(inp);
          } else {
            existing.value = data.token;
          }
        });
      }

      return;
    }

    updateStatus("error");
    setCaptchaSolvedState(false);
    sendCaptchaStat("failed");
    startCaptcha("error");
  } catch (error) {
    console.error("Unable to validate captcha", error);
    updateStatus("error");
    setCaptchaSolvedState(false);
  }
}

function createPiece(piece) {
  let canvas = document.createElement("canvas");
  canvas.width = pieceWidth;
  canvas.height = pieceHeight;

  let ctx = canvas.getContext("2d");
  ctx.drawImage(
    img,
    piece.x * pieceWidth,
    piece.y * pieceHeight,
    pieceWidth,
    pieceHeight,
    0,
    0,
    pieceWidth,
    pieceHeight,
  );

  canvas.dataset.id = piece.id;
  canvas.draggable = true;

  canvas.addEventListener("dragstart", function (event) {
    event.dataTransfer.setData("text", canvas.dataset.id);
  });

  canvas.addEventListener("dragover", function (event) {
    event.preventDefault();
  });

  canvas.addEventListener("drop", function (event) {
    event.preventDefault();

    let draggedId = event.dataTransfer.getData("text");
    let targetId = canvas.dataset.id;

    if (draggedId === targetId) return;

    let draggedPos = -1;
    let targetPos = -1;

    for (let i = 0; i < currentOrder.length; i++) {
      if (currentOrder[i].id == draggedId) draggedPos = i;
      if (currentOrder[i].id == targetId) targetPos = i;
    }

    if (draggedPos === -1 || targetPos === -1) return;

    let temp = currentOrder[draggedPos];
    currentOrder[draggedPos] = currentOrder[targetPos];
    currentOrder[targetPos] = temp;

    setCaptchaSolvedState(false);
    drawPieces();
  });

  return canvas;
}

function drawPieces() {
  container.innerHTML = "";
  for (let i = 0; i < currentOrder.length; i++) {
    let piece = createPiece(currentOrder[i]);
    container.appendChild(piece);
  }
}

async function startCaptcha(resetType = "reset") {
  container.innerHTML = "";
  setCaptchaSolvedState(false);

  if (resetType !== "error") {
    status.textContent = "";
    status.classList.remove("success-message", "fail-message");
  }

  try {
    await loadCaptchaImage();
  } catch (error) {
    console.error(error);
    status.textContent = "Unable to load captcha image.";
    status.classList.remove("success-message");
    status.classList.add("fail-message");
    return;
  }

  pieceWidth = img.width / 2;
  pieceHeight = img.height / 2;

  do {
    currentOrder = shuffle(pieces);
  } while (
    currentOrder[0].id === 1 &&
    currentOrder[1].id === 2 &&
    currentOrder[2].id === 3 &&
    currentOrder[3].id === 4
  );

  drawPieces();

  if (resetType !== "error") {
    updateStatus("base");
  }
}

if (container && status && resetBtn && validateBtn) {
  startCaptcha();

  resetBtn.addEventListener("click", async () => {
    await sendCaptchaStat("reseted");
    startCaptcha("reset");
  });

  validateBtn.addEventListener("click", validateCaptcha);
}
