const CACHE_NAME = "cloudchallenge-v1";
const urlsToCache = [
    // CSS
    "/css/adicionar_amigos.css",
    "/css/amigos.css",
    "/css/aula.css",
    "/css/aulas.css",
    "/css/config.css",
    "/css/dark_mode.css",
    "/css/esperar_competitivo.css",
    "/css/esperar_jogador.css",
    "/css/feedback.css",
    "/css/finalizar.css",
    "/css/index.css",
    "/css/jogar.css",
    "/css/login.css",
    "/css/loja.css",
    "/css/menu.css",
    "/css/participe_jogo.css",
    "/css/perfil.css",
    "/css/ranking.css",
    "/css/reset.css",
    "/css/responder_quiz.css",
    "/css/resultado_partida.css",
    "/css/subscribe.css",

    // Imagens
    "./assets/images/fundo_amigos.png",
    "./assets/images/fundo.jpg",
    "./assets/images/fundo2.png",
    "./assets/images/fundoJogueComAmigo.png",
    "./assets/images/fundoLoja.png",
    "./assets/images/fundoMontanha.png",
    "./assets/images/fundoMontanha1.png",
    "./assets/images/fundoMontanha2.png",
    "./assets/images/fundoMontanha3.png",
    "./assets/images/fundoQuizComp.png",
    "./assets/images/img_home.png",
    "./assets/images/logo.png",

    //icons
    "./assets/icons/logopwa.png",
    "./assets/icons/logoDDM.png",

    //avatares
    "./assets/images/avatares/1.png",
    "./assets/images/avatares/2.png",
    "./assets/images/avatares/3.png",
    "./assets/images/avatares/4.png",   
    "./assets/images/avatares/5.png",
    "./assets/images/avatares/6.png",
    "./assets/images/avatares/7.png",
    "./assets/images/avatares/8.png",
    "./assets/images/avatares/9.png",
    "./assets/images/avatares/10.png",
    "./assets/images/avatares/11.png",
    "./assets/images/avatares/12.png",
    "./assets/images/avatares/13.png",
    "./assets/images/avatares/14.png",
    "./assets/images/avatares/15.png",
    "./assets/images/avatares/16.png",
    "./assets/images/avatares/17.png",
    "./assets/images/avatares/18.png",
    "./assets/images/avatares/19.png",
    "./assets/images/avatares/20.png",
    "./assets/images/avatares/21.png",
    "./assets/images/avatares/22.png",
    "./assets/images/avatares/23.png",
    "./assets/images/avatares/24.png",
    "./assets/images/avatares/25.png",
    "./assets/images/avatares/26.png",
    "./assets/images/avatares/27.png",
    "./assets/images/avatares/novo (2).png",
    "./assets/images/avatares/novo.png",
    "./assets/images/avatares/novo3.png",
    "./assets/images/avatares/novo4.png"

];

self.addEventListener("install", (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(urlsToCache);
        })
    );
});

self.addEventListener("fetch", (event) => {
    // Ignora requisições PHP
    if (event.request.url.endsWith(".php")) return;

    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});
