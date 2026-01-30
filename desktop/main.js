const { app, BrowserWindow, dialog } = require("electron");
const { spawn } = require("child_process");
const http = require("http");
const path = require("path");

const URL = "http://localhost:8080";

function runDockerComposeUp() {
  return new Promise((resolve, reject) => {
    // Ejecuta: docker compose up -d  en la carpeta raíz del proyecto (ajusta path si hace falta)
    const projectRoot = path.resolve(__dirname, ".."); // asumiendo desktop/ al lado del compose
    const child = spawn("docker", ["compose", "up", "-d"], {
      cwd: projectRoot,
      shell: true,
      stdio: "inherit"
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
        // si responde, aunque sea 404, ya hay servidor
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

  // Carga algo bonito mientras arranca
  win.loadURL("data:text/html,<h3 style='font-family:sans-serif'>Arrancando...</h3>");

  try {
    await runDockerComposeUp();     // si NO quieres que Electron levante Docker, comenta esta línea
    await waitForHttp(URL, 60000);
    await win.loadURL(URL);
  } catch (e) {
    dialog.showErrorBox("No se pudo iniciar la app", String(e.message || e));
    app.quit();
  }
}

app.whenReady().then(createWindow);

app.on("window-all-closed", () => {
  // Importante: NO hacemos docker compose down (tu caso)
  app.quit();
});
