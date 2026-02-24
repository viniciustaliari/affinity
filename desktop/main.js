const { app, BrowserWindow, dialog } = require("electron");
const { spawn } = require("child_process");
const http = require("http");
const os = require("os");
const path = require("path");

const URL = "http://localhost:8080";

const BAD_IFACE_RE = /(docker|wsl|vethernet|loopback|virtual|vmware|hyper-v|tailscale|hamachi|zerotier)/i;
const GOOD_IFACE_RE = /(wi-?fi|wlan|ethernet|lan|eth|en)/i;

function isPrivate172(ip) {
  const parts = ip.split(".").map(Number);
  return parts.length === 4 && parts[0] === 172 && parts[1] >= 16 && parts[1] <= 31;
}

function isCandidateIp(ip) {
  return (
    typeof ip === "string" &&
    ip !== "" &&
    ip !== "127.0.0.1" &&
    !ip.startsWith("169.254.")
  );
}

function ipScore(ip) {
  if (ip.startsWith("192.168.")) return 30;
  if (ip.startsWith("10.")) return 20;
  if (isPrivate172(ip)) return 10;
  return 1;
}

function detectHostIp() {
  const nets = os.networkInterfaces();
  const candidates = [];

  Object.entries(nets).forEach(([iface, addresses]) => {
    if (!Array.isArray(addresses)) return;

    addresses.forEach((addr) => {
      const family = typeof addr.family === "string" ? addr.family : String(addr.family);
      if (family !== "IPv4") return;
      if (addr.internal) return;
      if (!isCandidateIp(addr.address)) return;

      const bad = BAD_IFACE_RE.test(iface);
      const good = GOOD_IFACE_RE.test(iface);
      let score = ipScore(addr.address);
      if (good) score += 5;
      if (bad) score -= 100;

      candidates.push({ iface, ip: addr.address, score });
    });
  });

  if (!candidates.length) return "";
  candidates.sort((a, b) => b.score - a.score);
  return candidates[0].ip;
}

function runDockerComposeUp() {
  return new Promise((resolve, reject) => {
    const projectRoot = path.resolve(__dirname, "..");
    const hostIp = detectHostIp();
    const env = { ...process.env };

    if (hostIp) {
      env.HOST_IP = hostIp;
      console.log(`[Affinity] HOST_IP detected: ${hostIp}`);
    } else {
      console.log("[Affinity] HOST_IP auto-detect failed. Using compose fallback.");
    }

    const child = spawn("docker", ["compose", "up", "-d"], {
      cwd: projectRoot,
      shell: true,
      stdio: "inherit",
      env
    });

    child.on("exit", (code) => {
      if (code === 0) resolve();
      else reject(new Error(`docker compose up -d falló (code=${code})`));
    });
  });
}

function waitForHttp(url, timeoutMs = 60000) {
  return new Promise((resolve, reject) => {
    const start = Date.now();

    const tick = () => {
      const req = http.get(url, (res) => {
        res.resume();
        if (res.statusCode && res.statusCode < 500) return resolve();
        if (Date.now() - start > timeoutMs) return reject(new Error("Timeout esperando a la app"));
        setTimeout(tick, 500);
      });

      req.on("error", () => {
        if (Date.now() - start > timeoutMs) return reject(new Error("Timeout esperando a la app"));
        setTimeout(tick, 500);
      });

      req.end();
    };

    tick();
  });
}

async function createWindow() {
  const win = new BrowserWindow({
    width: 1200,
    height: 800,
    autoHideMenuBar: true
  });

  win.loadURL("data:text/html,<h3 style='font-family:sans-serif'>Arrancando...</h3>");

  try {
    await runDockerComposeUp();
    await waitForHttp(URL, 60000);
    await win.loadURL(URL);
  } catch (e) {
    dialog.showErrorBox("No se pudo iniciar la app", String(e.message || e));
    app.quit();
  }
}

app.whenReady().then(createWindow);

app.on("window-all-closed", () => {
  app.quit();
});
