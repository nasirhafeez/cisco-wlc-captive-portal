<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$barcodeFailure = 0;

if($_GET) {
    $_SESSION['switch_url'] = $_GET['switch_url'];
    $_SESSION['ap_mac'] = $_GET['ap_mac'];
    $_SESSION['client_mac'] = $_GET['client_mac'];
    $_SESSION['wlan'] = $_GET['wlan'];
    $_SESSION['redirect'] = $_GET['redirect'];
    $_SESSION['statusCode'] = $_GET['statusCode'];

    $_SESSION["user_type"] = "new";
}

$host_ip = $_SERVER['HOST_IP'];
$db_user = $_SERVER['DB_USER'];
$db_pass = $_SERVER['DB_PASS'];
$db_name = $_SERVER['DB_NAME'];

$con = mysqli_connect($host_ip, $db_user, $db_pass, $db_name);

if (mysqli_connect_errno()) {
    echo "Failed to connect to SQL: " . mysqli_connect_error();
}

$result = mysqli_query($con, "SELECT * FROM `users` WHERE mac='$_SESSION[client_mac]'");

if ($result->num_rows >= 1) {
    $row=mysqli_fetch_array($result);

    $last_updated = $row[2];

    mysqli_close($con);

    $date1 = DateTime::createFromFormat('Y-m-d H:i:s', $last_updated);
    $current_date = date("Y-m-d H:i:s");
    $date2 = DateTime::createFromFormat('Y-m-d H:i:s', $current_date);

    $interval = date_diff($date1, $date2);
    $interval_days = $interval->days;

    if($interval_days < 2) {
        $_SESSION["user_type"] = "repeat_recent";
        header("Location: auth.php");
    }
    else {
        $_SESSION["user_type"] = "repeat_old";
    }
}
else {
    mysqli_close($con);
}

/*
Table schema

mysqli_query($con, "
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac` varchar(45) NOT NULL,
  `last_updated` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
)");
*/

if (isset($_POST['apisubmit'])) {
    $barcode = $_POST['barcode'];
    $target = "https://wifi.equinox.com/api/login";

    $postData = [
        "barcode" => $barcode
    ];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $target,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    if ($response !== false) {
        $json = json_decode($response, true);
        if ($json['Success'] == 1) {
            header("Location: auth.php");
        } else {
            $barcodeFailure = 1;
        }
    }
    else {
        die("Error: check with your network administrator");
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><script type="text/javascript">window.NREUM||(NREUM={});NREUM.info = {"beacon":"bam-cell.nr-data.net","errorBeacon":"bam-cell.nr-data.net","licenseKey":"325801c55a","applicationID":"4149307","transactionName":"ZVNbZhQFD0EHAhFcV1wZdGQlSy1dAQgLdldcQktdCggEQEkoC1FdSg==","queueTime":0,"applicationTime":2,"agent":"","atts":""}</script><script type="text/javascript">(window.NREUM||(NREUM={})).init={ajax:{deny_list:["bam-cell.nr-data.net"]}};(window.NREUM||(NREUM={})).loader_config={xpid:"Ug4CVFFXGwcBUFhRBwY=",licenseKey:"325801c55a",applicationID:"4149307"};window.NREUM||(NREUM={}),__nr_require=function(t,e,n){function r(n){if(!e[n]){var i=e[n]={exports:{}};t[n][0].call(i.exports,function(e){var i=t[n][1][e];return r(i||e)},i,i.exports)}return e[n].exports}if("function"==typeof __nr_require)return __nr_require;for(var i=0;i<n.length;i++)r(n[i]);return r}({1:[function(t,e,n){function r(t){try{s.console&&console.log(t)}catch(e){}}var i,o=t("ee"),a=t(28),s={};try{i=localStorage.getItem("__nr_flags").split(","),console&&"function"==typeof console.log&&(s.console=!0,i.indexOf("dev")!==-1&&(s.dev=!0),i.indexOf("nr_dev")!==-1&&(s.nrDev=!0))}catch(c){}s.nrDev&&o.on("internal-error",function(t){r(t.stack)}),s.dev&&o.on("fn-err",function(t,e,n){r(n.stack)}),s.dev&&(r("NR AGENT IN DEVELOPMENT MODE"),r("flags: "+a(s,function(t,e){return t}).join(", ")))},{}],2:[function(t,e,n){function r(t,e,n,r,s){try{p?p-=1:i(s||new UncaughtException(t,e,n),!0)}catch(f){try{o("ierr",[f,c.now(),!0])}catch(d){}}return"function"==typeof u&&u.apply(this,a(arguments))}function UncaughtException(t,e,n){this.message=t||"Uncaught error with no additional information",this.sourceURL=e,this.line=n}function i(t,e){var n=e?null:c.now();o("err",[t,n])}var o=t("handle"),a=t(29),s=t("ee"),c=t("loader"),f=t("gos"),u=window.onerror,d=!1,l="nr@seenError";if(!c.disabled){var p=0;c.features.err=!0,t(1),window.onerror=r;try{throw new Error}catch(h){"stack"in h&&(t(10),t(9),"addEventListener"in window&&t(6),c.xhrWrappable&&t(11),d=!0)}s.on("fn-start",function(t,e,n){d&&(p+=1)}),s.on("fn-err",function(t,e,n){d&&!n[l]&&(f(n,l,function(){return!0}),this.thrown=!0,i(n))}),s.on("fn-end",function(){d&&!this.thrown&&p>0&&(p-=1)}),s.on("internal-error",function(t){o("ierr",[t,c.now(),!0])})}},{}],3:[function(t,e,n){var r=t("loader");r.disabled||(r.features.ins=!0)},{}],4:[function(t,e,n){function r(){var t=new PerformanceObserver(function(t,e){var n=t.getEntries();s(v,[n])});try{t.observe({entryTypes:["resource"]})}catch(e){}}function i(t){if(s(v,[window.performance.getEntriesByType(w)]),window.performance["c"+l])try{window.performance[h](m,i,!1)}catch(t){}else try{window.performance[h]("webkit"+m,i,!1)}catch(t){}}function o(t){}if(window.performance&&window.performance.timing&&window.performance.getEntriesByType){var a=t("ee"),s=t("handle"),c=t(10),f=t(9),u=t(5),d=t(19),l="learResourceTimings",p="addEventListener",h="removeEventListener",m="resourcetimingbufferfull",v="bstResource",w="resource",g="-start",y="-end",x="fn"+g,b="fn"+y,E="bstTimer",R="pushState",S=t("loader");if(!S.disabled){S.features.stn=!0,t(8),"addEventListener"in window&&t(6);var O=NREUM.o.EV;a.on(x,function(t,e){var n=t[0];n instanceof O&&(this.bstStart=S.now())}),a.on(b,function(t,e){var n=t[0];n instanceof O&&s("bst",[n,e,this.bstStart,S.now()])}),c.on(x,function(t,e,n){this.bstStart=S.now(),this.bstType=n}),c.on(b,function(t,e){s(E,[e,this.bstStart,S.now(),this.bstType])}),f.on(x,function(){this.bstStart=S.now()}),f.on(b,function(t,e){s(E,[e,this.bstStart,S.now(),"requestAnimationFrame"])}),a.on(R+g,function(t){this.time=S.now(),this.startPath=location.pathname+location.hash}),a.on(R+y,function(t){s("bstHist",[location.pathname+location.hash,this.startPath,this.time])}),u()?(s(v,[window.performance.getEntriesByType("resource")]),r()):p in window.performance&&(window.performance["c"+l]?window.performance[p](m,i,d(!1)):window.performance[p]("webkit"+m,i,d(!1))),document[p]("scroll",o,d(!1)),document[p]("keypress",o,d(!1)),document[p]("click",o,d(!1))}}},{}],5:[function(t,e,n){e.exports=function(){return"PerformanceObserver"in window&&"function"==typeof window.PerformanceObserver}},{}],6:[function(t,e,n){function r(t){for(var e=t;e&&!e.hasOwnProperty(u);)e=Object.getPrototypeOf(e);e&&i(e)}function i(t){s.inPlace(t,[u,d],"-",o)}function o(t,e){return t[1]}var a=t("ee").get("events"),s=t("wrap-function")(a,!0),c=t("gos"),f=XMLHttpRequest,u="addEventListener",d="removeEventListener";e.exports=a,"getPrototypeOf"in Object?(r(document),r(window),r(f.prototype)):f.prototype.hasOwnProperty(u)&&(i(window),i(f.prototype)),a.on(u+"-start",function(t,e){var n=t[1];if(null!==n&&("function"==typeof n||"object"==typeof n)){var r=c(n,"nr@wrapped",function(){function t(){if("function"==typeof n.handleEvent)return n.handleEvent.apply(n,arguments)}var e={object:t,"function":n}[typeof n];return e?s(e,"fn-",null,e.name||"anonymous"):n});this.wrapped=t[1]=r}}),a.on(d+"-start",function(t){t[1]=this.wrapped||t[1]})},{}],7:[function(t,e,n){function r(t,e,n){var r=t[e];"function"==typeof r&&(t[e]=function(){var t=o(arguments),e={};i.emit(n+"before-start",[t],e);var a;e[m]&&e[m].dt&&(a=e[m].dt);var s=r.apply(this,t);return i.emit(n+"start",[t,a],s),s.then(function(t){return i.emit(n+"end",[null,t],s),t},function(t){throw i.emit(n+"end",[t],s),t})})}var i=t("ee").get("fetch"),o=t(29),a=t(28);e.exports=i;var s=window,c="fetch-",f=c+"body-",u=["arrayBuffer","blob","json","text","formData"],d=s.Request,l=s.Response,p=s.fetch,h="prototype",m="nr@context";d&&l&&p&&(a(u,function(t,e){r(d[h],e,f),r(l[h],e,f)}),r(s,"fetch",c),i.on(c+"end",function(t,e){var n=this;if(e){var r=e.headers.get("content-length");null!==r&&(n.rxSize=r),i.emit(c+"done",[null,e],n)}else i.emit(c+"done",[t],n)}))},{}],8:[function(t,e,n){var r=t("ee").get("history"),i=t("wrap-function")(r);e.exports=r;var o=window.history&&window.history.constructor&&window.history.constructor.prototype,a=window.history;o&&o.pushState&&o.replaceState&&(a=o),i.inPlace(a,["pushState","replaceState"],"-")},{}],9:[function(t,e,n){var r=t("ee").get("raf"),i=t("wrap-function")(r),o="equestAnimationFrame";e.exports=r,i.inPlace(window,["r"+o,"mozR"+o,"webkitR"+o,"msR"+o],"raf-"),r.on("raf-start",function(t){t[0]=i(t[0],"fn-")})},{}],10:[function(t,e,n){function r(t,e,n){t[0]=a(t[0],"fn-",null,n)}function i(t,e,n){this.method=n,this.timerDuration=isNaN(t[1])?0:+t[1],t[0]=a(t[0],"fn-",this,n)}var o=t("ee").get("timer"),a=t("wrap-function")(o),s="setTimeout",c="setInterval",f="clearTimeout",u="-start",d="-";e.exports=o,a.inPlace(window,[s,"setImmediate"],s+d),a.inPlace(window,[c],c+d),a.inPlace(window,[f,"clearImmediate"],f+d),o.on(c+u,r),o.on(s+u,i)},{}],11:[function(t,e,n){function r(t,e){d.inPlace(e,["onreadystatechange"],"fn-",s)}function i(){var t=this,e=u.context(t);t.readyState>3&&!e.resolved&&(e.resolved=!0,u.emit("xhr-resolved",[],t)),d.inPlace(t,y,"fn-",s)}function o(t){x.push(t),m&&(E?E.then(a):w?w(a):(R=-R,S.data=R))}function a(){for(var t=0;t<x.length;t++)r([],x[t]);x.length&&(x=[])}function s(t,e){return e}function c(t,e){for(var n in t)e[n]=t[n];return e}t(6);var f=t("ee"),u=f.get("xhr"),d=t("wrap-function")(u),l=t(19),p=NREUM.o,h=p.XHR,m=p.MO,v=p.PR,w=p.SI,g="readystatechange",y=["onload","onerror","onabort","onloadstart","onloadend","onprogress","ontimeout"],x=[];e.exports=u;var b=window.XMLHttpRequest=function(t){var e=new h(t);try{u.emit("new-xhr",[e],e),e.addEventListener(g,i,l(!1))}catch(n){try{u.emit("internal-error",[n])}catch(r){}}return e};if(c(h,b),b.prototype=h.prototype,d.inPlace(b.prototype,["open","send"],"-xhr-",s),u.on("send-xhr-start",function(t,e){r(t,e),o(e)}),u.on("open-xhr-start",r),m){var E=v&&v.resolve();if(!w&&!v){var R=1,S=document.createTextNode(R);new m(a).observe(S,{characterData:!0})}}else f.on("fn-end",function(t){t[0]&&t[0].type===g||a()})},{}],12:[function(t,e,n){function r(t){if(!s(t))return null;var e=window.NREUM;if(!e.loader_config)return null;var n=(e.loader_config.accountID||"").toString()||null,r=(e.loader_config.agentID||"").toString()||null,f=(e.loader_config.trustKey||"").toString()||null;if(!n||!r)return null;var h=p.generateSpanId(),m=p.generateTraceId(),v=Date.now(),w={spanId:h,traceId:m,timestamp:v};return(t.sameOrigin||c(t)&&l())&&(w.traceContextParentHeader=i(h,m),w.traceContextStateHeader=o(h,v,n,r,f)),(t.sameOrigin&&!u()||!t.sameOrigin&&c(t)&&d())&&(w.newrelicHeader=a(h,m,v,n,r,f)),w}function i(t,e){return"00-"+e+"-"+t+"-01"}function o(t,e,n,r,i){var o=0,a="",s=1,c="",f="";return i+"@nr="+o+"-"+s+"-"+n+"-"+r+"-"+t+"-"+a+"-"+c+"-"+f+"-"+e}function a(t,e,n,r,i,o){var a="btoa"in window&&"function"==typeof window.btoa;if(!a)return null;var s={v:[0,1],d:{ty:"Browser",ac:r,ap:i,id:t,tr:e,ti:n}};return o&&r!==o&&(s.d.tk=o),btoa(JSON.stringify(s))}function s(t){return f()&&c(t)}function c(t){var e=!1,n={};if("init"in NREUM&&"distributed_tracing"in NREUM.init&&(n=NREUM.init.distributed_tracing),t.sameOrigin)e=!0;else if(n.allowed_origins instanceof Array)for(var r=0;r<n.allowed_origins.length;r++){var i=h(n.allowed_origins[r]);if(t.hostname===i.hostname&&t.protocol===i.protocol&&t.port===i.port){e=!0;break}}return e}function f(){return"init"in NREUM&&"distributed_tracing"in NREUM.init&&!!NREUM.init.distributed_tracing.enabled}function u(){return"init"in NREUM&&"distributed_tracing"in NREUM.init&&!!NREUM.init.distributed_tracing.exclude_newrelic_header}function d(){return"init"in NREUM&&"distributed_tracing"in NREUM.init&&NREUM.init.distributed_tracing.cors_use_newrelic_header!==!1}function l(){return"init"in NREUM&&"distributed_tracing"in NREUM.init&&!!NREUM.init.distributed_tracing.cors_use_tracecontext_headers}var p=t(25),h=t(14);e.exports={generateTracePayload:r,shouldGenerateTrace:s}},{}],13:[function(t,e,n){function r(t){var e=this.params,n=this.metrics;if(!this.ended){this.ended=!0;for(var r=0;r<l;r++)t.removeEventListener(d[r],this.listener,!1);e.aborted||(n.duration=a.now()-this.startTime,this.loadCaptureCalled||4!==t.readyState?null==e.status&&(e.status=0):o(this,t),n.cbTime=this.cbTime,s("xhr",[e,n,this.startTime,this.endTime,"xhr"],this))}}function i(t,e){var n=c(e),r=t.params;r.hostname=n.hostname,r.port=n.port,r.protocol=n.protocol,r.host=n.hostname+":"+n.port,r.pathname=n.pathname,t.parsedOrigin=n,t.sameOrigin=n.sameOrigin}function o(t,e){t.params.status=e.status;var n=v(e,t.lastSize);if(n&&(t.metrics.rxSize=n),t.sameOrigin){var r=e.getResponseHeader("X-NewRelic-App-Data");r&&(t.params.cat=r.split(", ").pop())}t.loadCaptureCalled=!0}var a=t("loader");if(a.xhrWrappable&&!a.disabled){var s=t("handle"),c=t(14),f=t(12).generateTracePayload,u=t("ee"),d=["load","error","abort","timeout"],l=d.length,p=t("id"),h=t(20),m=t(18),v=t(15),w=t(19),g=NREUM.o.REQ,y=window.XMLHttpRequest;a.features.xhr=!0,t(11),t(7),u.on("new-xhr",function(t){var e=this;e.totalCbs=0,e.called=0,e.cbTime=0,e.end=r,e.ended=!1,e.xhrGuids={},e.lastSize=null,e.loadCaptureCalled=!1,e.params=this.params||{},e.metrics=this.metrics||{},t.addEventListener("load",function(n){o(e,t)},w(!1)),h&&(h>34||h<10)||t.addEventListener("progress",function(t){e.lastSize=t.loaded},w(!1))}),u.on("open-xhr-start",function(t){this.params={method:t[0]},i(this,t[1]),this.metrics={}}),u.on("open-xhr-end",function(t,e){"loader_config"in NREUM&&"xpid"in NREUM.loader_config&&this.sameOrigin&&e.setRequestHeader("X-NewRelic-ID",NREUM.loader_config.xpid);var n=f(this.parsedOrigin);if(n){var r=!1;n.newrelicHeader&&(e.setRequestHeader("newrelic",n.newrelicHeader),r=!0),n.traceContextParentHeader&&(e.setRequestHeader("traceparent",n.traceContextParentHeader),n.traceContextStateHeader&&e.setRequestHeader("tracestate",n.traceContextStateHeader),r=!0),r&&(this.dt=n)}}),u.on("send-xhr-start",function(t,e){var n=this.metrics,r=t[0],i=this;if(n&&r){var o=m(r);o&&(n.txSize=o)}this.startTime=a.now(),this.listener=function(t){try{"abort"!==t.type||i.loadCaptureCalled||(i.params.aborted=!0),("load"!==t.type||i.called===i.totalCbs&&(i.onloadCalled||"function"!=typeof e.onload))&&i.end(e)}catch(n){try{u.emit("internal-error",[n])}catch(r){}}};for(var s=0;s<l;s++)e.addEventListener(d[s],this.listener,w(!1))}),u.on("xhr-cb-time",function(t,e,n){this.cbTime+=t,e?this.onloadCalled=!0:this.called+=1,this.called!==this.totalCbs||!this.onloadCalled&&"function"==typeof n.onload||this.end(n)}),u.on("xhr-load-added",function(t,e){var n=""+p(t)+!!e;this.xhrGuids&&!this.xhrGuids[n]&&(this.xhrGuids[n]=!0,this.totalCbs+=1)}),u.on("xhr-load-removed",function(t,e){var n=""+p(t)+!!e;this.xhrGuids&&this.xhrGuids[n]&&(delete this.xhrGuids[n],this.totalCbs-=1)}),u.on("xhr-resolved",function(){this.endTime=a.now()}),u.on("addEventListener-end",function(t,e){e instanceof y&&"load"===t[0]&&u.emit("xhr-load-added",[t[1],t[2]],e)}),u.on("removeEventListener-end",function(t,e){e instanceof y&&"load"===t[0]&&u.emit("xhr-load-removed",[t[1],t[2]],e)}),u.on("fn-start",function(t,e,n){e instanceof y&&("onload"===n&&(this.onload=!0),("load"===(t[0]&&t[0].type)||this.onload)&&(this.xhrCbStart=a.now()))}),u.on("fn-end",function(t,e){this.xhrCbStart&&u.emit("xhr-cb-time",[a.now()-this.xhrCbStart,this.onload,e],e)}),u.on("fetch-before-start",function(t){function e(t,e){var n=!1;return e.newrelicHeader&&(t.set("newrelic",e.newrelicHeader),n=!0),e.traceContextParentHeader&&(t.set("traceparent",e.traceContextParentHeader),e.traceContextStateHeader&&t.set("tracestate",e.traceContextStateHeader),n=!0),n}var n,r=t[1]||{};"string"==typeof t[0]?n=t[0]:t[0]&&t[0].url?n=t[0].url:window.URL&&t[0]&&t[0]instanceof URL&&(n=t[0].href),n&&(this.parsedOrigin=c(n),this.sameOrigin=this.parsedOrigin.sameOrigin);var i=f(this.parsedOrigin);if(i&&(i.newrelicHeader||i.traceContextParentHeader))if("string"==typeof t[0]||window.URL&&t[0]&&t[0]instanceof URL){var o={};for(var a in r)o[a]=r[a];o.headers=new Headers(r.headers||{}),e(o.headers,i)&&(this.dt=i),t.length>1?t[1]=o:t.push(o)}else t[0]&&t[0].headers&&e(t[0].headers,i)&&(this.dt=i)}),u.on("fetch-start",function(t,e){this.params={},this.metrics={},this.startTime=a.now(),this.dt=e,t.length>=1&&(this.target=t[0]),t.length>=2&&(this.opts=t[1]);var n,r=this.opts||{},o=this.target;"string"==typeof o?n=o:"object"==typeof o&&o instanceof g?n=o.url:window.URL&&"object"==typeof o&&o instanceof URL&&(n=o.href),i(this,n);var s=(""+(o&&o instanceof g&&o.method||r.method||"GET")).toUpperCase();this.params.method=s,this.txSize=m(r.body)||0}),u.on("fetch-done",function(t,e){this.endTime=a.now(),this.params||(this.params={}),this.params.status=e?e.status:0;var n;"string"==typeof this.rxSize&&this.rxSize.length>0&&(n=+this.rxSize);var r={txSize:this.txSize,rxSize:n,duration:a.now()-this.startTime};s("xhr",[this.params,r,this.startTime,this.endTime,"fetch"],this)})}},{}],14:[function(t,e,n){var r={};e.exports=function(t){if(t in r)return r[t];var e=document.createElement("a"),n=window.location,i={};e.href=t,i.port=e.port;var o=e.href.split("://");!i.port&&o[1]&&(i.port=o[1].split("/")[0].split("@").pop().split(":")[1]),i.port&&"0"!==i.port||(i.port="https"===o[0]?"443":"80"),i.hostname=e.hostname||n.hostname,i.pathname=e.pathname,i.protocol=o[0],"/"!==i.pathname.charAt(0)&&(i.pathname="/"+i.pathname);var a=!e.protocol||":"===e.protocol||e.protocol===n.protocol,s=e.hostname===document.domain&&e.port===n.port;return i.sameOrigin=a&&(!e.hostname||s),"/"===i.pathname&&(r[t]=i),i}},{}],15:[function(t,e,n){function r(t,e){var n=t.responseType;return"json"===n&&null!==e?e:"arraybuffer"===n||"blob"===n||"json"===n?i(t.response):"text"===n||""===n||void 0===n?i(t.responseText):void 0}var i=t(18);e.exports=r},{}],16:[function(t,e,n){function r(){}function i(t,e,n,r){return function(){return u.recordSupportability("API/"+e+"/called"),o(t+e,[f.now()].concat(s(arguments)),n?null:this,r),n?void 0:this}}var o=t("handle"),a=t(28),s=t(29),c=t("ee").get("tracer"),f=t("loader"),u=t(21),d=NREUM;"undefined"==typeof window.newrelic&&(newrelic=d);var l=["setPageViewName","setCustomAttribute","setErrorHandler","finished","addToTrace","inlineHit","addRelease"],p="api-",h=p+"ixn-";a(l,function(t,e){d[e]=i(p,e,!0,"api")}),d.addPageAction=i(p,"addPageAction",!0),d.setCurrentRouteName=i(p,"routeName",!0),e.exports=newrelic,d.interaction=function(){return(new r).get()};var m=r.prototype={createTracer:function(t,e){var n={},r=this,i="function"==typeof e;return o(h+"tracer",[f.now(),t,n],r),function(){if(c.emit((i?"":"no-")+"fn-start",[f.now(),r,i],n),i)try{return e.apply(this,arguments)}catch(t){throw c.emit("fn-err",[arguments,this,t],n),t}finally{c.emit("fn-end",[f.now()],n)}}}};a("actionText,setName,setAttribute,save,ignore,onEnd,getContext,end,get".split(","),function(t,e){m[e]=i(h,e)}),newrelic.noticeError=function(t,e){"string"==typeof t&&(t=new Error(t)),u.recordSupportability("API/noticeError/called"),o("err",[t,f.now(),!1,e])}},{}],17:[function(t,e,n){function r(t){if(NREUM.init){for(var e=NREUM.init,n=t.split("."),r=0;r<n.length-1;r++)if(e=e[n[r]],"object"!=typeof e)return;return e=e[n[n.length-1]]}}e.exports={getConfiguration:r}},{}],18:[function(t,e,n){e.exports=function(t){if("string"==typeof t&&t.length)return t.length;if("object"==typeof t){if("undefined"!=typeof ArrayBuffer&&t instanceof ArrayBuffer&&t.byteLength)return t.byteLength;if("undefined"!=typeof Blob&&t instanceof Blob&&t.size)return t.size;if(!("undefined"!=typeof FormData&&t instanceof FormData))try{return JSON.stringify(t).length}catch(e){return}}}},{}],19:[function(t,e,n){var r=!1;try{var i=Object.defineProperty({},"passive",{get:function(){r=!0}});window.addEventListener("testPassive",null,i),window.removeEventListener("testPassive",null,i)}catch(o){}e.exports=function(t){return r?{passive:!0,capture:!!t}:!!t}},{}],20:[function(t,e,n){var r=0,i=navigator.userAgent.match(/Firefox[\/\s](\d+\.\d+)/);i&&(r=+i[1]),e.exports=r},{}],21:[function(t,e,n){function r(t,e){var n=[a,t,{name:t},e];return o("storeMetric",n,null,"api"),n}function i(t,e){var n=[s,t,{name:t},e];return o("storeEventMetrics",n,null,"api"),n}var o=t("handle"),a="sm",s="cm";e.exports={constants:{SUPPORTABILITY_METRIC:a,CUSTOM_METRIC:s},recordSupportability:r,recordCustom:i}},{}],22:[function(t,e,n){function r(){return s.exists&&performance.now?Math.round(performance.now()):(o=Math.max((new Date).getTime(),o))-a}function i(){return o}var o=(new Date).getTime(),a=o,s=t(30);e.exports=r,e.exports.offset=a,e.exports.getLastTimestamp=i},{}],23:[function(t,e,n){function r(t){return!(!t||!t.protocol||"file:"===t.protocol)}e.exports=r},{}],24:[function(t,e,n){function r(t,e){var n=t.getEntries();n.forEach(function(t){"first-paint"===t.name?l("timing",["fp",Math.floor(t.startTime)]):"first-contentful-paint"===t.name&&l("timing",["fcp",Math.floor(t.startTime)])})}function i(t,e){var n=t.getEntries();if(n.length>0){var r=n[n.length-1];if(c&&c<r.startTime)return;l("lcp",[r])}}function o(t){t.getEntries().forEach(function(t){t.hadRecentInput||l("cls",[t])})}function a(t){if(t instanceof v&&!g){var e=Math.round(t.timeStamp),n={type:t.type};e<=p.now()?n.fid=p.now()-e:e>p.offset&&e<=Date.now()?(e-=p.offset,n.fid=p.now()-e):e=p.now(),g=!0,l("timing",["fi",e,n])}}function s(t){"hidden"===t&&(c=p.now(),l("pageHide",[c]))}if(!("init"in NREUM&&"page_view_timing"in NREUM.init&&"enabled"in NREUM.init.page_view_timing&&NREUM.init.page_view_timing.enabled===!1)){var c,f,u,d,l=t("handle"),p=t("loader"),h=t(27),m=t(19),v=NREUM.o.EV;if("PerformanceObserver"in window&&"function"==typeof window.PerformanceObserver){f=new PerformanceObserver(r);try{f.observe({entryTypes:["paint"]})}catch(w){}u=new PerformanceObserver(i);try{u.observe({entryTypes:["largest-contentful-paint"]})}catch(w){}d=new PerformanceObserver(o);try{d.observe({type:"layout-shift",buffered:!0})}catch(w){}}if("addEventListener"in document){var g=!1,y=["click","keydown","mousedown","pointerdown","touchstart"];y.forEach(function(t){document.addEventListener(t,a,m(!1))})}h(s)}},{}],25:[function(t,e,n){function r(){function t(){return e?15&e[n++]:16*Math.random()|0}var e=null,n=0,r=window.crypto||window.msCrypto;r&&r.getRandomValues&&(e=r.getRandomValues(new Uint8Array(31)));for(var i,o="xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx",a="",s=0;s<o.length;s++)i=o[s],"x"===i?a+=t().toString(16):"y"===i?(i=3&t()|8,a+=i.toString(16)):a+=i;return a}function i(){return a(16)}function o(){return a(32)}function a(t){function e(){return n?15&n[r++]:16*Math.random()|0}var n=null,r=0,i=window.crypto||window.msCrypto;i&&i.getRandomValues&&Uint8Array&&(n=i.getRandomValues(new Uint8Array(31)));for(var o=[],a=0;a<t;a++)o.push(e().toString(16));return o.join("")}e.exports={generateUuid:r,generateSpanId:i,generateTraceId:o}},{}],26:[function(t,e,n){function r(t,e){if(!i)return!1;if(t!==i)return!1;if(!e)return!0;if(!o)return!1;for(var n=o.split("."),r=e.split("."),a=0;a<r.length;a++)if(r[a]!==n[a])return!1;return!0}var i=null,o=null,a=/Version\/(\S+)\s+Safari/;if(navigator.userAgent){var s=navigator.userAgent,c=s.match(a);c&&s.indexOf("Chrome")===-1&&s.indexOf("Chromium")===-1&&(i="Safari",o=c[1])}e.exports={agent:i,version:o,match:r}},{}],27:[function(t,e,n){function r(t){function e(){t(s&&document[s]?document[s]:document[o]?"hidden":"visible")}"addEventListener"in document&&a&&document.addEventListener(a,e,i(!1))}var i=t(19);e.exports=r;var o,a,s;"undefined"!=typeof document.hidden?(o="hidden",a="visibilitychange",s="visibilityState"):"undefined"!=typeof document.msHidden?(o="msHidden",a="msvisibilitychange"):"undefined"!=typeof document.webkitHidden&&(o="webkitHidden",a="webkitvisibilitychange",s="webkitVisibilityState")},{}],28:[function(t,e,n){function r(t,e){var n=[],r="",o=0;for(r in t)i.call(t,r)&&(n[o]=e(r,t[r]),o+=1);return n}var i=Object.prototype.hasOwnProperty;e.exports=r},{}],29:[function(t,e,n){function r(t,e,n){e||(e=0),"undefined"==typeof n&&(n=t?t.length:0);for(var r=-1,i=n-e||0,o=Array(i<0?0:i);++r<i;)o[r]=t[e+r];return o}e.exports=r},{}],30:[function(t,e,n){e.exports={exists:"undefined"!=typeof window.performance&&window.performance.timing&&"undefined"!=typeof window.performance.timing.navigationStart}},{}],ee:[function(t,e,n){function r(){}function i(t){function e(t){return t&&t instanceof r?t:t?f(t,c,a):a()}function n(n,r,i,o,a){if(a!==!1&&(a=!0),!p.aborted||o){t&&a&&t(n,r,i);for(var s=e(i),c=m(n),f=c.length,u=0;u<f;u++)c[u].apply(s,r);var l=d[y[n]];return l&&l.push([x,n,r,s]),s}}function o(t,e){g[t]=m(t).concat(e)}function h(t,e){var n=g[t];if(n)for(var r=0;r<n.length;r++)n[r]===e&&n.splice(r,1)}function m(t){return g[t]||[]}function v(t){return l[t]=l[t]||i(n)}function w(t,e){p.aborted||u(t,function(t,n){e=e||"feature",y[n]=e,e in d||(d[e]=[])})}var g={},y={},x={on:o,addEventListener:o,removeEventListener:h,emit:n,get:v,listeners:m,context:e,buffer:w,abort:s,aborted:!1};return x}function o(t){return f(t,c,a)}function a(){return new r}function s(){(d.api||d.feature)&&(p.aborted=!0,d=p.backlog={})}var c="nr@context",f=t("gos"),u=t(28),d={},l={},p=e.exports=i();e.exports.getOrSetContext=o,p.backlog=d},{}],gos:[function(t,e,n){function r(t,e,n){if(i.call(t,e))return t[e];var r=n();if(Object.defineProperty&&Object.keys)try{return Object.defineProperty(t,e,{value:r,writable:!0,enumerable:!1}),r}catch(o){}return t[e]=r,r}var i=Object.prototype.hasOwnProperty;e.exports=r},{}],handle:[function(t,e,n){function r(t,e,n,r){i.buffer([t],r),i.emit(t,e,n)}var i=t("ee").get("handle");e.exports=r,r.ee=i},{}],id:[function(t,e,n){function r(t){var e=typeof t;return!t||"object"!==e&&"function"!==e?-1:t===window?0:a(t,o,function(){return i++})}var i=1,o="nr@id",a=t("gos");e.exports=r},{}],loader:[function(t,e,n){function r(){if(!T++){var t=O.info=NREUM.info,e=v.getElementsByTagName("script")[0];if(setTimeout(f.abort,3e4),!(t&&t.licenseKey&&t.applicationID&&e))return f.abort();c(R,function(e,n){t[e]||(t[e]=n)});var n=a();s("mark",["onload",n+O.offset],null,"api"),s("timing",["load",n]);var r=v.createElement("script");0===t.agent.indexOf("http://")||0===t.agent.indexOf("https://")?r.src=t.agent:r.src=h+"://"+t.agent,e.parentNode.insertBefore(r,e)}}function i(){"complete"===v.readyState&&o()}function o(){s("mark",["domContent",a()+O.offset],null,"api")}var a=t(22),s=t("handle"),c=t(28),f=t("ee"),u=t(26),d=t(23),l=t(17),p=t(19),h=l.getConfiguration("ssl")===!1?"http":"https",m=window,v=m.document,w="addEventListener",g="attachEvent",y=m.XMLHttpRequest,x=y&&y.prototype,b=!d(m.location);NREUM.o={ST:setTimeout,SI:m.setImmediate,CT:clearTimeout,XHR:y,REQ:m.Request,EV:m.Event,PR:m.Promise,MO:m.MutationObserver};var E=""+location,R={beacon:"bam.nr-data.net",errorBeacon:"bam.nr-data.net",agent:"js-agent.newrelic.com/nr-1212.min.js"},S=y&&x&&x[w]&&!/CriOS/.test(navigator.userAgent),O=e.exports={offset:a.getLastTimestamp(),now:a,origin:E,features:{},xhrWrappable:S,userAgent:u,disabled:b};if(!b){t(16),t(24),v[w]?(v[w]("DOMContentLoaded",o,p(!1)),m[w]("load",r,p(!1))):(v[g]("onreadystatechange",i),m[g]("onload",r)),s("mark",["firstbyte",a.getLastTimestamp()],null,"api");var T=0}},{}],"wrap-function":[function(t,e,n){function r(t,e){function n(e,n,r,c,f){function nrWrapper(){var o,a,u,l;try{a=this,o=d(arguments),u="function"==typeof r?r(o,a):r||{}}catch(p){i([p,"",[o,a,c],u],t)}s(n+"start",[o,a,c],u,f);try{return l=e.apply(a,o)}catch(h){throw s(n+"err",[o,a,h],u,f),h}finally{s(n+"end",[o,a,l],u,f)}}return a(e)?e:(n||(n=""),nrWrapper[l]=e,o(e,nrWrapper,t),nrWrapper)}function r(t,e,r,i,o){r||(r="");var s,c,f,u="-"===r.charAt(0);for(f=0;f<e.length;f++)c=e[f],s=t[c],a(s)||(t[c]=n(s,u?c+r:r,i,c,o))}function s(n,r,o,a){if(!h||e){var s=h;h=!0;try{t.emit(n,r,o,e,a)}catch(c){i([c,n,r,o],t)}h=s}}return t||(t=u),n.inPlace=r,n.flag=l,n}function i(t,e){e||(e=u);try{e.emit("internal-error",t)}catch(n){}}function o(t,e,n){if(Object.defineProperty&&Object.keys)try{var r=Object.keys(t);return r.forEach(function(n){Object.defineProperty(e,n,{get:function(){return t[n]},set:function(e){return t[n]=e,e}})}),e}catch(o){i([o],n)}for(var a in t)p.call(t,a)&&(e[a]=t[a]);return e}function a(t){return!(t&&t instanceof Function&&t.apply&&!t[l])}function s(t,e){var n=e(t);return n[l]=t,o(t,n,u),n}function c(t,e,n){var r=t[e];t[e]=s(r,n)}function f(){for(var t=arguments.length,e=new Array(t),n=0;n<t;++n)e[n]=arguments[n];return e}var u=t("ee"),d=t(29),l="nr@original",p=Object.prototype.hasOwnProperty,h=!1;e.exports=r,e.exports.wrapFunction=s,e.exports.wrapInPlace=c,e.exports.argsToArray=f},{}]},{},["loader",2,13,4,3]);</script>
        <title>Equinox WiFi</title>
        <meta name="description" content="Equinox WiFi" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="assets/site.css" />
        <script type="text/javascript" src="assets/zepto.min.js"></script>
    </head>
<body>

    <svg display="none" width="0" height="0" preserveaspectratio="none" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <defs>
            <symbol id="icon-wifi" viewbox="128 128 768 768">
                <title>wifi</title>
                <path class="path1" d="M297.984 551.168l60.416 60.416c39.339-39.339 93.653-63.744 153.6-63.744s114.261 24.405 153.6 63.744l60.416-60.416c-54.784-54.784-130.432-88.661-214.016-88.661s-159.232 33.877-214.016 88.661zM512 267.819c-137.344 0-261.675 55.68-351.659 145.664l60.501 60.501c74.581-74.624 177.579-120.832 291.157-120.832s216.576 46.208 291.157 120.832l60.501-60.501c-89.984-89.984-214.315-145.664-351.659-145.664zM512 640c-35.328 0-64 28.672-64 64s28.672 64 64 64 64-28.672 64-64-28.672-64-64-64z"></path>
            </symbol>
        </defs>
    </svg>

    <main id="main">
        <header>
            <svg class="icon">
                <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#icon-wifi"></use>
            </svg>
            <h1>WiFi Login</h1>
        </header>
        <p>Equinox offers free WiFi to its members.</p>
        <p>To access, sign in with your membership details.</p>
        <form id="myform" method="post" action="<?php echo $_SERVER["PHP_SELF"];?>">
            <section>
                <div class="input-group">
                    <input id="barcodeId" class="form-control" name="barcode" type="text" placeholder="Barcode #" />
                    <div class="input-group-addon">
                        <a class="icon-info tooltips">
                        <span>
                            Need help? <br />
                            You can also ask the front desk for your barcode ID.
                        </span>
                        </a>
                    </div>
                </div>
                <p class="terms">
                    By signing in to Equinox WiFi, you agree to the <span id="terms">terms of use</span>
                </p>
                <?php
                if ($barcodeFailure == 1) { ?>
                <p id="error-message">*Please enter a correct barcode id</p>
                <?php
                }
                ?>
            </section>
            <button id="submitBtn" name="apisubmit" type="submit">Sign In</button>
        </form>
    </main>

    <section id="aup" class="terms">
        <h3>
            <span class="icon-close"></span>
            GUEST ACCESS ACCEPTABLE USE POLICY
        </h3>
        <div class="content">
            <p>This Guest Access Acceptable Use Policy (“AUP”) applies to everyone with access to Equinox’s WiFi Network (collectively, the “Service”, which includes any associated services, systems, software, equipment, content and other media, documentation, updates or upgrades). This AUP is intended to protect Equinox, our Service, other Service users and the Internet community generally, against improper or illegal activities.&nbsp; You are responsible for complying with this AUP in connection with your access to and use of the Service. If you do not accept this AUP, you may not access or use the Service.</p>
            <p>You are only authorized to use the Service for legal and appropriate purposes and activities. You may not use the Service for any unlawful, improper or criminal purpose or activity.&nbsp; Equinox reserves the right to cooperate with legal authorities and other third parties in the investigation of any suspected or alleged criminal purpose or activity or civil claims.&nbsp; Equinox reserves the right to block, suspend or terminate your access to the Service for any reason at any time, including, without limitation, violation of this AUP.</p>
            <p>The following are examples of violations of this AUP but are not intended to be exhaustive.</p>
            <p>You agree that you will not use the Service to:</p>
            <ul>
                <li>transmit any material (by uploading, posting, email or otherwise) that is unlawful, threatening, abusive, harassing, tortuous, defamatory, obscene, libelous, hateful or otherwise objectionable;</li>
                <li>impersonate any person or entity or falsely or fraudulently act in any manner;</li>
                <li>transmit any unsolicited or unauthorized advertising or promotional materials, junk mail ,spam or any other form of solicitation;</li>
                <li>collect or store, or attempt to collect or store any personal data of third parties without their knowledge or consent;</li>
                <li>transmit, post, display, distribute, or execute any materials or content that violates any copyright, right of publicity, patent, trademark, trade secret or other intellectual property rights of any other party;</li>
                <li>violate the terms of any applicable software or license agreements.</li>
                <li>modify, distribute, transmit or otherwise use any content, software or other materials for which you do not have the rights to do so.</li>
                <li>violate, or attempt to violate, the security or integrity of the Service, or to use the Service to violate the security or integrity of any other system.</li>
                <li>to attempt to log into or otherwise obtain or seek to obtain unauthorized access to any account, information or computer system. This includes, for example: (i) accessing data or information not intended for you or your use, (ii) attempting to probe, scan, or test the vulnerability of any system, subsystem or network, and (iii) tampering, hacking, modifying or otherwise corrupting, circumventing or breaching security or authentication measures without proper authorization;</li>
                <li>to interfere with or attempt to interfere with service to any user, host, or network, whether by use of any program, script, command, or otherwise. This includes interfering with service to any user, host, or network by means of overloading, “flooding,” “mailbombing,” or “crashing” any computer system, or causing a user’s screen to “scroll” faster than the user is able to follow to type to it, “trapping” users at any URL or site, or taking any action that has a similarly disruptive effect as any of the foregoing;</li>
                <li>to introduce or transmit material that contains viruses, trojan horses, worms, time bombs, cancelbots or any other computer programming routines or engines with the intent or effect of damaging, destroying, disrupting or otherwise impairing a computer’s functionality or the operation of the Service or any other service;</li>
                <li>to enable another party to access Equinox systems or services;</li>
                <li>to interfere with, intercept or expropriate any system, data or information;</li>
                <li>use or introduce any network monitoring or scanning tools to monitor or scan any Equinox Service; or</li>
                <li>gain access to any corporate network account, password, or data that you have not been granted permission to access.</li>
            </ul>
            <p>The Service is provided on an “as is” and “as available” basis, without warranties of any kind.&nbsp; Equinox does not warrant that the Service will be uninterrupted, error-free, or free of viruses or other harmful components. Equinox makes no express warranties and You waive all implied warranties including, but not limited to, warranties of title, non infringement, merchantability, and fitness for a particular purpose regarding any merchandise, information or service provided through Equinox or the internet generally.</p>
            <p>You expressly acknowledge that there are, and You assume all responsibility related to, security, privacy and confidentiality risks inherent in wireless communications and technology.&nbsp; You are solely responsible for maintaining the confidentiality of your passwords and account information and the transmission of any confidential or proprietary information, and Equinox does not make any assurances or warranties relating thereto.&nbsp; You also are responsible for assuring that any non-Equinox provided system you use to connect to the Service is physically secured from unauthorized users and kept up to date with appropriate security updates.</p>
            <p>Equinox, its employees, agents, vendors, and licensors are not liable for any costs or damages arising, either directly or indirectly, from your use of the Service or the internet, specifically including any direct, indirect, incidental, exemplary, multiple, special, punitive or consequential damages.</p>
            <p>Equinox may, at its sole discretion, modify the terms and conditions of this AUP at any time. You agree to periodically review this AUP to maintain awareness of any modifications. By continuing to use the Service, you accept and agree to any and all such modifications.</p>
        </div>
    </section>

    <script>
        Zepto(function ($) {

            // function IsValid() {
            //     var barcode = $("#barcodeId").val();
            //     if (barcode.length == 0 || barcode == "undefined") {
            //         $('#barcodeId, .input-group-addon').addClass('input-error');
            //         $('#error-message').html('*Please enter a correct barcode id');
            //         $('#error-message').css('visibility', 'visible');
            //         return false;
            //     }
            //     else {
            //         $('#barcodeId, .input-group-addon').removeClass('input-error');
            //         $('#error-message').css('visibility', 'hidden');
            //         return true;
            //     }
            // }
            // function loadAction() {
            //     var url = window.location.href;
            //     var args = {};
            //     var query = location.search.substring(1);
            //     var pairs = query.split("&");
            //     var i, pos, argname, value;
            //
            //     for (i = 0; i < pairs.length; i++) {
            //         pos = pairs[i].indexOf('=');
            //         if (pos === -1) continue;
            //         argname = pairs[i].substring(0, pos);
            //         value = pairs[i].substring(pos + 1);
            //         args[argname] = decodeURIComponent(value);
            //     }
            //     document.forms[0].action = args.switch_url;
            // }

            // function submitAction() {
            //     var link = document.location.href;
            //     var searchString = "redirect=";
            //     var equalIndex = link.indexOf(searchString);
            //     var redirectUrl = "";
            //
            //     if (document.forms[0].action == "") {
            //         var url = window.location.href;
            //         var args = new Object();
            //         var query = location.search.substring(1);
            //         var pairs = query.split("&");
            //         for (var i = 0; i < pairs.length; i++) {
            //             var pos = pairs[i].indexOf('=');
            //             if (pos == -1) continue;
            //             var argname = pairs[i].substring(0, pos);
            //             var value = pairs[i].substring(pos + 1);
            //             args[argname] = unescape(value);
            //         }
            //         document.forms[0].action = args.switch_url;
            //     }
            //     if (equalIndex >= 0) {
            //         equalIndex += searchString.length;
            //         redirectUrl = "";
            //         redirectUrl += link.substring(equalIndex);
            //     }
            //     if (redirectUrl.length > 255)
            //         redirectUrl = redirectUrl.substring(0, 255);
            //     document.forms[0].redirect_url.value = redirectUrl;
            //     document.forms[0].buttonClicked.value = 4;
            //     document.forms[0].submit();
            // }

            // $('#myform').submit(function (e) {
            //     if (!IsValid())
            //         return false;
            //     e.preventDefault();
            //     $('#submitBtn').html('Loading...');
            //
            //     function hide_error_message() {
            //         $('#barcodeId, .input-group-addon').removeClass('input-error');
            //         $('#error-message').css('visibility', 'hidden');
            //     }
            //
            //     function show_error_message(s) {
            //         if (!s || s.match(/(found|exist)/)) {
            //             s = 'We couldn\'t find that membership. Please try again.';
            //         }
            //         $('#barcodeId, .input-group-addon').addClass('input-error');
            //         $('#error-message').text(s).css('visibility', 'visible');
            //     }
            //
            //     var form = $(this).serializeArray();
            //     $.ajax({
            //         type: 'POST',
            //         url: 'https://wifi.equinox.com/api/login',
            //         data: form,
            //         dataType: 'json',
            //         success: function (data) {
            //             if (data['Success']) {
            //                 hide_error_message();
            //                 submitAction();
            //             } else {
            //                 show_error_message(data['Message']);
            //             }
            //         },
            //         error: function (xhr, type) {
            //             show_error_message('An unknown error occurred. Please try again.');
            //         },
            //         complete: function (xhr, status) {
            //             $('#submitBtn').html('Sign In');
            //         }
            //     });
            // });

            // $('#submitBtn').on('click touch', function (e) {
            //     e.preventDefault();
            //     $('#myform').submit();
            // });

            $('#terms').on('click touch', function (e) {
                $('#main').toggle();
                $('#aup').toggle();
            });

            $('.icon-close').on('click touch', function (e) {
                $('#aup').toggle();
                $('#main').toggle();
            });

            // loadAction();
        });
    </script>

</body>
</html> 