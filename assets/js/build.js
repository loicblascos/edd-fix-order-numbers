/*!
* EDD Fix Order Numbers
*
* @package   EDD - Fix Order Numbers
* @author    Loïc Blascos
* @copyright 2019-2021 Loïc Blascos
*
*/
!function(){var e,r,o,n=document.querySelector("#edd-fix-order-numbers-progress-indicator"),t=document.querySelector("#edd-fix-order-numbers-progress"),s=document.querySelector("#edd-fix-order-numbers-process"),c=(null===(e=window)||void 0===e?void 0:e.edd_fix_order_numbers)||{},d=!1,i=function(){window.AbortController&&(r=new AbortController,o=r.signal)},u=function e(r){fetch(ajaxurl,{method:"post",body:r,signal:o}).then((function(e){if(!e.ok)throw a(c.process,"",c.error),Error(e.statusText);return e.json()})).then((function(o){o.success?o.progress>=100?a(c.process,100,c.complete):(a(c.cancel,o.progress,o.message),r.set("offset",o.offset),e(r)):a(c.process,"",o.message||c.error)})).catch((function(){return null}))},a=function(e,r,o){s.textContent=e,n.textContent=o,t.hidden=""===o,t.value=r,d=""!==r&&r>=0&&r<100};s&&s.addEventListener("click",(function(){var e=new FormData;if(d)return r&&r.abort(),i(),void a(c.process,"","");e.append("offset",0),e.append("action","edd_fix_order_numbers"),e.append("nonce",c.nonce),a(c.cancel,0,"0%"),u(e)})),i()}();