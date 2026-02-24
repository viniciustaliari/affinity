<?php
include_once '../components/head.php';
require_once '../api/db.php';

$id_programa = isset($_GET['id']) ? intval($_GET['id']) : 0;
$programa = null;
$mediaItems = [];

function detectFileMimeType(string $path): string
{
    if ($path !== '' && is_file($path) && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            if (is_string($mime) && $mime !== '') {
                return strtolower(trim($mime));
            }
        }
    }
    return '';
}

function extensionFromMime(string $mime): string
{
    $map = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/svg+xml' => 'svg',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/x-matroska' => 'mkv',
    ];
    return $map[strtolower(trim($mime))] ?? '';
}

function normalizeDisplayNameByMime(string $name, string $mime): string
{
    $name = trim($name);
    if ($name === '') {
        return $name;
    }

    $expectedExt = extensionFromMime($mime);
    if ($expectedExt === '') {
        return $name;
    }

    $base = pathinfo($name, PATHINFO_FILENAME);
    if ($base === '') {
        return $name;
    }

    return $base . '.' . $expectedExt;
}

if ($id_programa > 0) {
    $programa = $database->get("programas", ["id", "nombre"], ["id" => $id_programa]);

    if ($programa) {
        $imagenes = $database->select("imagenes", ["id", "nombre", "nombre_original", "indice", "duracion"], [
            "id_programa" => $id_programa
        ]);

        $videos = $database->select("video", ["id", "nombre", "nombre_original", "indice", "mute", "repeat"], [
            "id_programa" => $id_programa
        ]);

        foreach ($imagenes as $img) {
            $storedName = basename((string)$img["nombre"]);
            if ($storedName === '') {
                continue;
            }

            $path = __DIR__ . '/../data/images/' . $storedName;
            if (!is_file($path)) {
                continue;
            }

            $mime = detectFileMimeType($path);
            $detectedType = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $displayName = trim((string)($img["nombre_original"] ?? ''));
            if ($displayName === '') {
                $displayName = $storedName;
            }
            $displayName = normalizeDisplayNameByMime($displayName, $mime);
            $durationSec = (float)($img["duracion"] ?? 3);
            if ($durationSec <= 0) {
                $durationSec = 3;
            }
            $mediaItems[] = [
                "type" => $detectedType,
                "id" => (int)$img["id"],
                "indice" => (int)($img["indice"] ?? 0),
                "nombre" => $displayName,
                "mute" => false,
                "duration" => $durationSec,
                "repeat" => 1,
                "src" => "/api/media_stream.php?bucket=images&name=" . rawurlencode($storedName)
            ];
        }

        foreach ($videos as $vid) {
            $storedName = basename((string)$vid["nombre"]);
            if ($storedName === '') {
                continue;
            }

            $path = __DIR__ . '/../data/videos/' . $storedName;
            if (!is_file($path)) {
                continue;
            }

            $mime = detectFileMimeType($path);
            $detectedType = str_starts_with($mime, 'image/') ? 'image' : 'video';
            $displayName = trim((string)($vid["nombre_original"] ?? ''));
            if ($displayName === '') {
                $displayName = $storedName;
            }
            $displayName = normalizeDisplayNameByMime($displayName, $mime);
            $repeat = (int)($vid["repeat"] ?? 1);
            if ($repeat <= 0) {
                $repeat = 1;
            }
            $mediaItems[] = [
                "type" => $detectedType,
                "id" => (int)$vid["id"],
                "indice" => (int)($vid["indice"] ?? 0),
                "nombre" => $displayName,
                "mute" => !empty($vid["mute"]),
                "duration" => 0,
                "repeat" => $repeat,
                "src" => "/api/media_stream.php?bucket=videos&name=" . rawurlencode($storedName)
            ];
        }

        usort($mediaItems, static function (array $a, array $b): int {
            $cmp = ($a["indice"] <=> $b["indice"]);
            if ($cmp !== 0) {
                return $cmp;
            }
            return $a["id"] <=> $b["id"];
        });
    }
}
?>

<style>
.preview-item {
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.preview-item.is-active {
    border-color: #f97316;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.25);
}

.sequence-media {
    width: 300px;
    max-width: 100%;
    height: auto;
    max-height: 100%;
    object-fit: contain;
}

.sequence-media-image {
    width: 300px;
    max-width: 100%;
    aspect-ratio: 9 / 16;
    height: auto;
    object-fit: contain;
}

.preview-back-arrow {
    position: relative;
}

.preview-back-arrow::after {
    content: "";
    position: absolute;
    inset: 0;
    border: 2px solid rgba(255, 255, 255, 0.92);
    border-radius: 9999px;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.preview-back-arrow svg {
    position: relative;
    z-index: 1;
}

.preview-back-arrow:hover {
    border-color: transparent;
    background-image:
        linear-gradient(#f97316, #f97316),
        linear-gradient(to right, #06b6d4, #3b82f6);
    background-origin: border-box;
    background-clip: padding-box, border-box;
}

.preview-back-arrow:hover::after {
    opacity: 1;
}
</style>

<main class="px-3">
    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 p-5 rounded-2xl min-h-[80vh]">
        <div class="bg-white rounded-2xl p-6 shadow-xl">
            <div class="grid grid-cols-3 items-center gap-4 mb-5">
                <div class="flex items-center justify-start">
                    <a href="/pages/programas.php"
                       class="preview-back-arrow z-20 w-16 h-16 rounded-full border-2 border-blue-400 bg-gradient-to-r from-cyan-500 to-blue-500 flex items-center justify-center text-white transition shadow-lg"
                       aria-label="Volver atras"
                       title="Volver atras">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5"></path>
                            <path d="M12 19L5 12L12 5"></path>
                        </svg>
                    </a>
                </div>
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-blue-800">Preview multimedia</h1>
                    <?php if ($programa): ?>
                        <p class="text-slate-700 mt-1">Programa: <?= htmlspecialchars((string)$programa["nombre"]) ?></p>
                    <?php endif; ?>
                </div>
                <div></div>
            </div>

            <?php if (!$programa): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-700">
                    Programa no encontrado.
                </div>
            <?php elseif (empty($mediaItems)): ?>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-slate-700">
                    Este programa no tiene contenido multimedia disponible para preview.
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2 mb-3">
                    <button id="btnStartSequence" class="bg-orange-500 text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-orange-600 transition">Ver secuencia</button>
                    <button id="btnStopSequence" class="bg-slate-200 text-slate-800 px-3 py-2 rounded-lg text-sm font-bold hover:bg-slate-300 transition">Detener</button>
                    <p id="sequenceStatus" class="text-xs text-slate-600 ml-2">Listo para reproducir.</p>
                </div>
                <div class="flex flex-col gap-2">
                    <?php foreach ($mediaItems as $item): ?>
                        <article
                            class="preview-item border rounded-xl p-2 bg-slate-50 flex items-center gap-3"
                            data-type="<?= htmlspecialchars((string)$item["type"]) ?>"
                            data-src="<?= htmlspecialchars((string)$item["src"]) ?>"
                            data-name="<?= htmlspecialchars((string)$item["nombre"]) ?>"
                            data-mute="<?= !empty($item["mute"]) ? '1' : '0' ?>"
                            data-index="<?= (int)($item["indice"] ?? 0) ?>"
                            data-duration="<?= (float)($item["duration"] ?? 3) ?>"
                            data-repeat="<?= (int)($item["repeat"] ?? 1) ?>"
                        >
                            <div class="shrink-0 flex items-center justify-center rounded-lg bg-black/5" style="width:140px;height:100px;">
                                <?php if ($item["type"] === "image"): ?>
                                    <img
                                        src="<?= htmlspecialchars((string)$item["src"]) ?>"
                                        alt="<?= htmlspecialchars((string)$item["nombre"]) ?>"
                                        class="object-contain rounded-lg"
                                        style="max-width:140px;max-height:100px;width:auto;height:auto;"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <video
                                        src="<?= htmlspecialchars((string)$item["src"]) ?>"
                                        class="rounded-lg bg-black object-contain"
                                        style="max-width:140px;max-height:100px;width:auto;height:auto;"
                                        controls
                                        playsinline
                                        preload="metadata"
                                        <?php if (!empty($item["mute"])): ?>muted<?php endif; ?>
                                    ></video>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-slate-700 break-all">
                                    #<?= (int)$item["indice"] ?> - <?= htmlspecialchars((string)$item["nombre"]) ?>
                                </p>
                                <p class="text-[10px] text-slate-500 uppercase mt-1">
                                    <?= htmlspecialchars((string)$item["type"]) ?>
                                </p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div id="sequenceModal" class="fixed inset-0 bg-black/80 z-[80] hidden flex items-center justify-center p-2">
    <div id="sequenceModalBackdrop" class="absolute inset-0"></div>
    <div class="relative z-[1] w-[340px] max-w-[96vw] max-h-[92vh] bg-black rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-black flex items-center justify-center p-1">
            <div class="w-full rounded-xl bg-slate-950/70 border border-slate-700 flex items-center justify-center overflow-hidden py-1">
                <div class="relative inline-flex items-center justify-center">
                    <button id="btnCloseSequenceModal" class="bg-orange-500 text-white w-6 h-6 rounded-full text-[11px] font-bold leading-none hover:bg-orange-600 transition" style="position:absolute; top:6px; right:6px; z-index:20;">X</button>
                    <img id="sequenceModalImage" src="" alt="Preview imagen" class="hidden sequence-media-image">
                    <video id="sequenceModalVideo" class="hidden sequence-media bg-black" controls playsinline preload="auto"></video>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const items = Array.from(document.querySelectorAll(".preview-item"));
    if (items.length === 0) return;

    const btnStart = document.getElementById("btnStartSequence");
    const btnStop = document.getElementById("btnStopSequence");
    const statusEl = document.getElementById("sequenceStatus");
    const sequenceModal = document.getElementById("sequenceModal");
    const sequenceModalBackdrop = document.getElementById("sequenceModalBackdrop");
    const btnCloseModal = document.getElementById("btnCloseSequenceModal");
    const btnStopModal = document.getElementById("btnStopSequenceModal");
    const modalStatusEl = document.getElementById("sequenceModalStatus");
    const modalItemLabelEl = document.getElementById("sequenceModalItemLabel");
    const modalImage = document.getElementById("sequenceModalImage");
    const modalVideo = document.getElementById("sequenceModalVideo");

    let running = false;
    let currentIndex = -1;
    let imageTimer = null;
    let modalVideoEndedHandler = null;
    let currentVideoRepeat = 0;
    let currentVideoRepeatTarget = 1;

    function setStatus(text) {
        if (statusEl) statusEl.textContent = text;
        if (modalStatusEl) modalStatusEl.textContent = text;
    }

    function setItemLabel(text) {
        if (modalItemLabelEl) modalItemLabelEl.textContent = text;
    }

    function openSequenceModal() {
        if (!sequenceModal) return;
        sequenceModal.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
    }

    function closeSequenceModal() {
        if (!sequenceModal) return;
        sequenceModal.classList.add("hidden");
        document.body.classList.remove("overflow-hidden");
    }

    function stopAllVideos() {
        if (!modalVideo) return;
        if (modalVideoEndedHandler) {
            modalVideo.removeEventListener("ended", modalVideoEndedHandler);
            modalVideoEndedHandler = null;
        }
        modalVideo.pause();
        try {
            modalVideo.currentTime = 0;
        } catch (e) {
            // ignore
        }
    }

    function clearActive() {
        items.forEach((item) => item.classList.remove("is-active"));
    }

    function showModalMedia(type, src, muted) {
        if (!modalImage || !modalVideo) return;

        if (type === "image") {
            modalVideo.pause();
            modalVideo.classList.add("hidden");
            modalImage.src = src;
            modalImage.classList.remove("hidden");
            return;
        }

        modalImage.classList.add("hidden");
        modalImage.removeAttribute("src");
        modalVideo.classList.remove("hidden");
        modalVideo.muted = !!muted;
        if (modalVideo.getAttribute("src") !== src) {
            modalVideo.setAttribute("src", src);
            modalVideo.load();
        }
    }

    function stopSequence() {
        running = false;
        currentIndex = -1;
        if (imageTimer) {
            clearTimeout(imageTimer);
            imageTimer = null;
        }
        clearActive();
        stopAllVideos();
        if (modalImage) {
            modalImage.classList.add("hidden");
            modalImage.removeAttribute("src");
        }
        if (modalVideo) {
            modalVideo.classList.add("hidden");
            modalVideo.removeAttribute("src");
            modalVideo.load();
        }
        setStatus("Secuencia detenida.");
        setItemLabel("Esperando inicio...");
    }

    function playSequenceAt(index) {
        if (!running) return;
        if (index >= items.length) {
            running = false;
            clearActive();
            setStatus("Secuencia finalizada.");
            setItemLabel("Secuencia completa.");
            return;
        }

        currentIndex = index;
        const item = items[index];
        const type = String(item.dataset.type || "");
        const src = String(item.dataset.src || "");
        const name = String(item.dataset.name || `Item ${index + 1}`);
        const mute = item.dataset.mute === "1";
        const duration = Math.max(1, Number(item.dataset.duration || "3"));
        const repeat = Math.max(1, Number(item.dataset.repeat || "1"));
        const mediaIndex = Number(item.dataset.index || index);

        clearActive();
        item.classList.add("is-active");
        item.scrollIntoView({ behavior: "smooth", block: "nearest" });
        setItemLabel(`#${mediaIndex} - ${name}`);

        if (imageTimer) {
            clearTimeout(imageTimer);
            imageTimer = null;
        }

        if (type === "image") {
            stopAllVideos();
            showModalMedia("image", src, false);
            setStatus(`Mostrando imagen ${index + 1}/${items.length} (${duration}s)`);
            imageTimer = setTimeout(() => {
                playSequenceAt(index + 1);
            }, duration * 1000);
            return;
        }

        if (!src) {
            playSequenceAt(index + 1);
            return;
        }

        stopAllVideos();
        showModalMedia("video", src, mute);
        if (!modalVideo) {
            playSequenceAt(index + 1);
            return;
        }

        currentVideoRepeat = 0;
        currentVideoRepeatTarget = repeat;

        const onEnded = () => {
            currentVideoRepeat += 1;
            if (!running) return;

            if (currentVideoRepeat < currentVideoRepeatTarget) {
                try {
                    modalVideo.currentTime = 0;
                } catch (e) {
                    // ignore
                }
                const retryPlay = modalVideo.play();
                if (retryPlay && typeof retryPlay.catch === "function") {
                    retryPlay.catch(() => {
                        playSequenceAt(index + 1);
                    });
                }
                setStatus(`Reproduciendo video ${index + 1}/${items.length} (repeticion ${currentVideoRepeat + 1}/${currentVideoRepeatTarget})`);
                return;
            }

            if (modalVideoEndedHandler) {
                modalVideo.removeEventListener("ended", modalVideoEndedHandler);
                modalVideoEndedHandler = null;
            }
            playSequenceAt(index + 1);
        };

        modalVideoEndedHandler = onEnded;
        modalVideo.addEventListener("ended", modalVideoEndedHandler);

        try {
            modalVideo.currentTime = 0;
        } catch (e) {
            // ignore
        }

        setStatus(`Reproduciendo video ${index + 1}/${items.length}`);
        const p = modalVideo.play();
        if (p && typeof p.catch === "function") {
            p.catch(() => {
                if (!running) return;

                if (!modalVideo.muted) {
                    modalVideo.muted = true;
                    const mutedRetry = modalVideo.play();
                    if (mutedRetry && typeof mutedRetry.catch === "function") {
                        mutedRetry.catch(() => {
                            if (modalVideoEndedHandler) {
                                modalVideo.removeEventListener("ended", modalVideoEndedHandler);
                                modalVideoEndedHandler = null;
                            }
                            setStatus("Video bloqueado por el navegador. Saltando al siguiente.");
                            playSequenceAt(index + 1);
                        });
                    } else {
                        setStatus(`Reproduciendo video ${index + 1}/${items.length} en mute`);
                    }
                    return;
                }

                if (modalVideoEndedHandler) {
                    modalVideo.removeEventListener("ended", modalVideoEndedHandler);
                    modalVideoEndedHandler = null;
                }
                setStatus("No se pudo iniciar el video automaticamente. Saltando al siguiente.");
                playSequenceAt(index + 1);
            });
        }
    }

    if (btnStart) {
        btnStart.addEventListener("click", () => {
            stopSequence();
            openSequenceModal();
            running = true;
            setStatus("Iniciando secuencia...");
            playSequenceAt(0);
        });
    }

    if (btnStop) {
        btnStop.addEventListener("click", () => {
            stopSequence();
        });
    }

    if (btnStopModal) {
        btnStopModal.addEventListener("click", () => {
            stopSequence();
        });
    }

    if (btnCloseModal) {
        btnCloseModal.addEventListener("click", () => {
            stopSequence();
            closeSequenceModal();
        });
    }

    if (sequenceModalBackdrop) {
        sequenceModalBackdrop.addEventListener("click", () => {
            stopSequence();
            closeSequenceModal();
        });
    }

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && sequenceModal && !sequenceModal.classList.contains("hidden")) {
            stopSequence();
            closeSequenceModal();
        }
    });
})();
</script>
