<?php
require_once 'autenticacao.php';require_once 'layout.php';exigirLogin();inicioPagina('Consumo da API');
?>
<div class="titulo-com-acao"><div><h1>Consumo da API REST</h1><p class="subtitulo">Leitura dos extintores cadastrados, com localização como referência quando não existe número confirmado.</p></div><a class="botao-link" href="api_extintores.php" target="_blank">Ver JSON da API</a></div>
<p><strong>Endpoint consultado:</strong> <code>api_extintores.php</code></p>
<div id="resultado-api" class="mensagem">Carregando dados da API...</div>
<p>Os métodos GET, POST, PUT e DELETE estão disponíveis conforme o perfil de acesso. Número, validade, capacidade, fabricante e tipo podem ser desconhecidos no levantamento inicial.</p>
<script>
const resultado=document.getElementById('resultado-api');
function escaparHtml(valor){const elemento=document.createElement('span');elemento.textContent=valor??'';return elemento.innerHTML;}
fetch('api_extintores.php').then(async r=>{const d=await r.json();if(!r.ok)throw new Error(d.erro||'Erro na API');return d;})
.then(registros=>{
    if (!registros.length){resultado.textContent='Nenhum extintor cadastrado.';return;}
    const linhas=registros.map(e=>{
        const tipo=e.tipo||'A confirmar';
        const cap=e.capacidade!==null?Number(e.capacidade).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})+' '+(/água|agua/i.test(tipo)?'L':'kg'):'Não informada';
        const rotulo=e.identificacao!==null?'Nº '+e.identificacao:'Por localização';
        return `<tr><td>${escaparHtml(rotulo)}</td><td>${escaparHtml(tipo)}</td><td>${escaparHtml(cap)}</td><td>${escaparHtml(e.andar)}º — ${escaparHtml(e.localizacao)}</td></tr>`;
    }).join('');
    resultado.className='tabela-responsiva';
    resultado.innerHTML=`<table><tr><th>Referência</th><th>Tipo</th><th>Capacidade</th><th>Localização</th></tr>${linhas}</table>`;
}).catch(e=>{resultado.className='mensagem erro';resultado.textContent='Não foi possível consultar a API: '+e.message;});
</script>
<?php fimPagina(); ?>
