<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-white leading-tight">
        🎰 Ruleta Navideña
    </h2>
</x-slot>

<button
    id="btnToggleNavbar"
    class="fixed top-24 right-4 z-50 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-xl shadow-lg">

</button>

<div class="min-h-screen flex flex-col justify-between bg-[url('/img/navidad-bg.png')] bg-repeat px-4">

<style>
/* ===== OCULTAR NAVBAR ===== */
body.navbar-hidden #navbar-global { display:none; }
body.navbar-hidden { overflow:hidden; }

/* ===== ESCENARIO ===== */
.slot-machine{
    display:flex;
    align-items:center;
    gap:80px;
    color:white;
}
.reels{ display:flex; gap:40px; }
.reel{
    width:520px;
    height:260px;
    overflow:hidden;
    border:6px solid #22c55e;
    border-radius:22px;
    background:#000;
    position:relative;
}
.reel ul{
    list-style:none;
    padding:0;
    margin:0;
    text-align:center;
    transition:transform 4.8s cubic-bezier(.08,.82,.17,1);
}
.reel li{
    height:80px;
    line-height:80px;
    font-size:34px;
    color:#777;
    opacity:.55;
}
.reel li.active{
    color:#22c55e;
    font-weight:800;
    opacity:1;
}
.window{
    position:absolute;
    top:90px;
    left:0;
    right:0;
    height:80px;
    border-top:4px solid #22c55e;
    border-bottom:4px solid #22c55e;
}
.lever{ width:60px; height:380px; }
.lever-stick{
    width:12px;
    height:260px;
    background:#ccc;
    margin:0 auto;
    border-radius:6px;
    transform-origin:top;
    transition:transform .25s ease;
}
.lever-ball{
    width:52px;
    height:52px;
    background:red;
    border-radius:50%;
    margin:14px auto 0;
}
.min-h-screen{ min-height:auto; }
.lever.down .lever-stick{ transform:rotate(32deg); }
.ultimo-item{
    background:#111;
    border-left:6px solid #22c55e;
    padding:12px 16px;
    border-radius:10px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 0 10px rgba(34,197,94,.4);
}

/* ===== ESTADÍSTICAS ===== */
.stat-card{
    background:#111;
    border-radius:14px;
    padding:14px 20px;
    text-align:center;
    box-shadow:0 0 15px rgba(34,197,94,.5);
}
.stat-number{
    font-size:30px;
    font-weight:800;
}
.slot-stage{
    background:#000;
    padding:10px 30px;
    border-radius:30px;
    box-shadow:0 0 70px rgba(34,197,94,.9);
    margin-top:10px;
}
</style>

<!-- 📊 ESTADÍSTICAS -->
<div class="mt-6">
    <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6 text-white">
        <div class="stat-card">
            👥 Participantes
            <div class="stat-number text-blue-400" id="statParticipantes">
                {{ $totalParticipantes }}
            </div>
        </div>
        <div class="stat-card">
            🎁 Premios Totales
            <div class="stat-number text-yellow-400" id="statPremios">
                {{ $totalPremios }}
            </div>
        </div>
        <div class="stat-card">
            ✅ Premios Entregados
            <div class="stat-number text-green-400" id="statEntregados">
                {{ $premiosEntregados }}
            </div>
        </div>
        <div class="stat-card">
            ⏳ Premios por Entregar
            <div class="stat-number text-red-400" id="statFaltantes">
                {{ $premiosFaltantes }}
            </div>
        </div>
    </div>
</div>

<!-- 🎰 RULETA -->
<div class="flex justify-center mt-4">
    <div class="slot-stage">
        <div class="slot-machine">
            <div class="reels">
                <div class="reel">
                    <div class="window"></div>
                    <ul id="reelNombre"></ul>
                </div>
                <div class="reel">
                    <div class="window"></div>
                    <ul id="reelPremio"></ul>
                </div>
            </div>

            <div class="lever" id="lever">
                <div class="lever-stick"></div>
                <div class="lever-ball"></div>
            </div>
        </div>
    </div>
</div>

<!-- ❌ NO ASISTIÓ -->
<div class="flex justify-center">
    <button
        id="btnNoAsistio"
        class="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl shadow-lg hidden">
        ❌ No asistió / Reiniciar
    </button>
</div>

<!-- 🏆 ÚLTIMOS GANADORES -->
<div class="fixed bottom-6 left-6 w-80 z-40">
    <div class="bg-black/80 rounded-2xl shadow-lg p-6 text-white">
        <h3 class="text-xl font-bold mb-4 text-center text-green-400">
            🏆 Últimos Ganadores
        </h3>

        <ul id="listaUltimos" class="space-y-3 text-lg">
            <li class="text-center text-gray-400">
                Aún no hay ganadores
            </li>
        </ul>
    </div>
</div>



<script>
/* =====================================================
   NAVBAR
===================================================== */
const btnNavbar = document.getElementById('btnToggleNavbar');
btnNavbar.addEventListener('click', () => {
    document.body.classList.toggle('navbar-hidden');
    btnNavbar.textContent =
        document.body.classList.contains('navbar-hidden')
            ? '⬇ Mostrar menú'
            : '⬆ Ocultar menú';
});

/* =====================================================
   CONFIG
===================================================== */
const DURACION_GIRO = 4800;
const STORAGE_KEY = 'ruleta_navidad_estado';

/* =====================================================
   ESTADO
===================================================== */
let bloqueado = false;
let fase = 'nombre';
let participanteId = null;
let nombreActual = null;
let premioActual = null;

const nombres = @json($nombres);
const premios = @json($premios);

const reelNombre = document.getElementById('reelNombre');
const reelPremio = document.getElementById('reelPremio');
const lever = document.getElementById('lever');
const btnNoAsistio = document.getElementById('btnNoAsistio');

/* =====================================================
   STORAGE
===================================================== */
function guardarEstado() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
        fase,
        participanteId,
        nombre: nombreActual,
        premio: premioActual
    }));
}

function limpiarEstado() {
    localStorage.removeItem(STORAGE_KEY);
}

/* =====================================================
   PREPARAR RULETA
===================================================== */
function preparar(reel, data) {
    reel.innerHTML = '';
    const lista = [];
    for (let i = 0; i < 25; i++) lista.push(...data);
    lista.forEach(txt => {
        const li = document.createElement('li');
        li.textContent = txt;
        reel.appendChild(li);
    });
}

preparar(reelNombre, nombres);
preparar(reelPremio, premios);

reelNombre.style.transition = 'none';
reelPremio.style.transition = 'none';

/* =====================================================
   PALANCA
===================================================== */
function accionar() {
    lever.classList.add('down');
    setTimeout(() => lever.classList.remove('down'), 300);
}

/* =====================================================
   GIRO ANIMADO
===================================================== */
function girar(reel, valor, callback) {
    const items = [...reel.children];
    items.forEach(i => i.classList.remove('active'));

    const posiciones = items
        .map((el, i) => el.textContent === valor ? i : null)
        .filter(i => i !== null);

    const index = posiciones[Math.floor(posiciones.length * 0.6)];

    reel.style.transition = 'none';
    reel.style.transform = `translateY(0)`;
    reel.offsetHeight;

    reel.style.transition = `transform ${DURACION_GIRO}ms cubic-bezier(.08,.82,.17,1)`;
    reel.style.transform = `translateY(-${index * 80 - 80}px)`;

    setTimeout(() => {
        items[index].classList.add('active');
        bloqueado = false;
        callback && callback();
    }, DURACION_GIRO);
}

/* =====================================================
   POSICIONAR (RESTORE REAL)
===================================================== */
function posicionar(reel, valor) {
    const items = [...reel.children];
    items.forEach(i => i.classList.remove('active'));

    const posiciones = items
        .map((el, i) => el.textContent === valor ? i : null)
        .filter(i => i !== null);

    if (!posiciones.length) return;

    const index = posiciones[Math.floor(posiciones.length * 0.6)];

    reel.style.transition = 'none';
    reel.style.transform = `translateY(-${index * 80 - 80}px)`;

    requestAnimationFrame(() => {
        items[index].classList.add('active');
        reel.style.transition = `transform ${DURACION_GIRO}ms cubic-bezier(.08,.82,.17,1)`;
    });
}

/* =====================================================
   TECLADO
===================================================== */
document.addEventListener('keydown', e => {
    if (e.key !== 'Enter' || e.repeat || bloqueado) return;

    // 🔄 SIEMPRE refresca estadísticas
    actualizarEstadisticas();

    accionar();

    if (fase === 'nombre') {
        girarNombre();
    } 
    else if (fase === 'premio') {
        girarPremio();
    } 
    else if (fase === 'fin') {
        reiniciar();
    }
});


/* =====================================================
   ENTER 1 → NOMBRE
===================================================== */
function girarNombre() {
    bloqueado = true;

    fetch("{{ route('rifa.girarNombre') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(d => {
        participanteId = d.id;
        nombreActual = d.ganador;

        girar(reelNombre, nombreActual, () => {
            fase = 'premio';
            btnNoAsistio.classList.remove('hidden');
            guardarEstado();
        });
    });
}

/* =====================================================
   ENTER 2 → PREMIO
===================================================== */
function girarPremio() {
    bloqueado = true;

    fetch("{{ route('rifa.girarPremio') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ participante_id: participanteId })
    })
    .then(r => r.json())
    .then(d => {
        premioActual = d.premio;

        girar(reelPremio, premioActual, () => {
            fase = 'fin';
            btnNoAsistio.classList.add('hidden');
            guardarEstado();
            actualizarEstadisticas();
            actualizarUltimos();
        });
    });
}

/* =====================================================
   ENTER 3 → REINICIAR
===================================================== */
function reiniciar() {
    bloqueado = true;

    [...reelNombre.children, ...reelPremio.children]
        .forEach(i => i.classList.remove('active'));

    participanteId = null;
    nombreActual = null;
    premioActual = null;
    fase = 'nombre';

    limpiarEstado();
    actualizarEstadisticas();
    actualizarUltimos();

    setTimeout(() => bloqueado = false, 300);
}



/* =====================================================
   NO ASISTIÓ
===================================================== */
btnNoAsistio.addEventListener('click', () => {
    if (!participanteId || bloqueado || fase !== 'premio') return;
    bloqueado = true;

    fetch("{{ route('rifa.noAsistio') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ participante_id: participanteId })
    }).then(() => reiniciar());
});

/* =====================================================
   RESTAURAR AL CARGAR
===================================================== */
(function restaurar() {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;

    try {
        const data = JSON.parse(raw);

        fase = data.fase;
        participanteId = data.participanteId;
        nombreActual = data.nombre;
        premioActual = data.premio;

        if (nombreActual) posicionar(reelNombre, nombreActual);
        if (premioActual) posicionar(reelPremio, premioActual);

        if (fase === 'premio') btnNoAsistio.classList.remove('hidden');
        else btnNoAsistio.classList.add('hidden');

    } catch {
        limpiarEstado();
    }
})();

/* =====================================================
   REPOSICIONAR SI SE VUELVE A LA PESTAÑA
===================================================== */
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        restaurarEstado();
    }
});

/* =====================================================
   RESTAURAR AL CARGAR
===================================================== */
function restaurarEstado() {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;

    try {
        const data = JSON.parse(raw);

        fase = data.fase;
        participanteId = data.participanteId;
        nombreActual = data.nombre;
        premioActual = data.premio;

        if (nombreActual) posicionar(reelNombre, nombreActual);
        if (premioActual) posicionar(reelPremio, premioActual);

        if (fase === 'premio') btnNoAsistio.classList.remove('hidden');
        else btnNoAsistio.classList.add('hidden');

    } catch {
        limpiarEstado();
    }
}

restaurarEstado();
actualizarEstadisticas();

/* =====================================================
   ACTUALIZAR ESTADÍSTICAS
===================================================== */

function actualizarEstadisticas() {
    fetch("{{ route('rifa.estadisticas') }}")
        .then(r => r.json())
        .then(d => {
            document.getElementById('statParticipantes').textContent = d.totalParticipantes;
            document.getElementById('statPremios').textContent = d.totalPremios;
            document.getElementById('statEntregados').textContent = d.premiosEntregados;
            document.getElementById('statFaltantes').textContent = d.premiosFaltantes;
        })
        .catch(err => console.error('Stats error:', err));
}
/* =====================================================
   ÚLTIMOS GANADORES
===================================================== */
function actualizarUltimos() {
    fetch("{{ route('rifa.ultimosGanadores') }}")
        .then(r => r.json())
        .then(lista => {
            const ul = document.getElementById('listaUltimos');
            if (!ul) return;

            ul.innerHTML = '';

            if (!lista.length) {
                ul.innerHTML = `<li class="text-center text-gray-400">
                    Aún no hay ganadores
                </li>`;
                return;
            }

            lista.forEach(g => {
                const li = document.createElement('li');
                li.className =
                    'flex justify-between bg-green-900/40 px-3 py-2 rounded-lg';

                li.innerHTML = `
                    <span>🎄 ${g.nombre}</span>
                    <span class="font-bold text-green-400">${g.premio}</span>
                `;
                ul.appendChild(li);
            });
        })
        .catch(err => console.error('Ultimos error:', err));
}



// Actualiza las estadísticas cada 5 segundos (5000 milisegundos)
setInterval(() => {
    actualizarEstadisticas();
    actualizarUltimos(); // 👈 NUEVO
}, 5000);



// Llama a la función de restauración al cargar el script
restaurarEstado();
</script>






</div>
</x-app-layout>
