<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        🎰 Ruleta Navideña
    </h2>
</x-slot>

<button
    id="btnToggleNavbar"
    class="fixed top-24 right-4 z-50 bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-xl shadow-lg">
    ⬆ Ocultar menú
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
.min-h-screen{
    min-height: auto;
}
.lever.down .lever-stick{ transform:rotate(32deg); }

/* ===== FOOTER ESTADÍSTICAS ===== */
.stats-footer{
    background:rgba(0,0,0,.85);
    border-top:4px solid #22c55e;
    padding:18px;
    margin-top:40px;
}
.stat-card{
    background:#111;
    border-radius:14px;
    padding:16px 24px;
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
    margin-top:10px;   /* ⬅️ PEGADO A ESTADÍSTICAS */
}
.stats-footer{
    margin-top:0;
    padding-top:10px;
    padding-bottom:10px;
}
.max-w-7xl{
    margin-top:0 !important;
}
header{
    margin-bottom:8px !important;
}
.stat-card{
    padding:12px 18px;
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
            🎁 Premios totales
            <div class="stat-number text-yellow-400" id="statPremios">
                {{ $totalPremios }}
            </div>
        </div>

        <div class="stat-card">
            ✅ Entregados
            <div class="stat-number text-green-400" id="statEntregados">
                {{ $premiosEntregados }}
            </div>
        </div>

        <div class="stat-card">
            ⏳ Faltantes
            <div class="stat-number text-red-400" id="statFaltantes">
                {{ $premiosFaltantes }}
            </div>
        </div>

    </div>
</div>

<!-- 🎰 RULETA -->
<div class="flex justify-center">
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





<script>
const btnNavbar=document.getElementById('btnToggleNavbar');
btnNavbar.addEventListener('click',()=>{
    document.body.classList.toggle('navbar-hidden');
    btnNavbar.textContent=document.body.classList.contains('navbar-hidden')
        ? '⬇ Mostrar menú'
        : '⬆ Ocultar menú';
});

let estado="nombre";        // controla la fase de ruleta
let bloqueado=false;
let participanteId=null;
let enterCount=0;           // contador de Enter

const nombres=@json($nombres);
const premios=@json($premios);

const reelNombre=document.getElementById('reelNombre');
const reelPremio=document.getElementById('reelPremio');
const lever=document.getElementById('lever');

function preparar(reel,data){
    reel.innerHTML='';
    let lista=[];
    for(let i=0;i<16;i++) lista.push(...data);
    lista.forEach(t=>{
        const li=document.createElement('li');
        li.textContent=t;
        reel.appendChild(li);
    });
}

preparar(reelNombre,nombres);
preparar(reelPremio,premios);

function girar(reel,valor){
    const items=[...reel.children];
    items.forEach(i=>i.classList.remove('active'));

    const idxs=items.map((el,i)=>el.textContent===valor?i:null).filter(i=>i!==null);
    const index=idxs[idxs.length-4];
    reel.style.transform=`translateY(-${index*80-80}px)`;
    setTimeout(()=>items[index].classList.add('active'),4500);
}

function actualizarEstadisticas() {
    fetch("{{ route('rifa.estadisticas') }}")
        .then(r => r.json())
        .then(d => {
            document.getElementById('statParticipantes').textContent = d.totalParticipantes;
            document.getElementById('statPremios').textContent = d.totalPremios;
            document.getElementById('statEntregados').textContent = d.premiosEntregados;
            document.getElementById('statFaltantes').textContent = d.premiosFaltantes;
        });
}


function accionar(){
    lever.classList.add('down');
    setTimeout(()=>lever.classList.remove('down'),320);
}

document.addEventListener('keydown', e => {

    // ❌ solo Enter
    if (e.key !== 'Enter') return;

    // ❌ ignora Enter sostenido
    if (e.repeat) return;

    // ❌ bloqueos de animación
    if (bloqueado) return;

    accionar();

    // lógica de 3 Enter
    enterCount++;

    if (enterCount === 1) {
        girarNombre();
    } 
    else if (enterCount === 2) {
        girarPremio();
    } 
    else if (enterCount === 3) {
        // quitar iluminación
        [...reelNombre.children, ...reelPremio.children]
            .forEach(i => i.classList.remove('active'));

        enterCount = 0;
        estado = "nombre";
    }
});

function girarNombre(){
    bloqueado=true;
    fetch("{{ route('rifa.girarNombre') }}",{
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
    })
    .then(r=>r.json())
    .then(d=>{
        girar(reelNombre,d.ganador);
        participanteId=d.id;
        estado="premio";
        bloqueado=false;
    });
}

function girarPremio(){
    bloqueado=true;
    fetch("{{ route('rifa.girarPremio') }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Content-Type':'application/json'
        },
        body:JSON.stringify({participante_id:participanteId})
    })
    .then(r=>r.json())
    .then(d=>{
        girar(reelPremio,d.premio);
        estado="reset";
        bloqueado=false;

        actualizarEstadisticas();
    });
}
</script>


</div>
</x-app-layout>
