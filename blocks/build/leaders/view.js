(()=>{"use strict";function e(){const s=document.getElementsByClassName("modal-open")[0],l=s.getElementsByClassName("close")[0];s.classList.remove("modal-open"),l.removeEventListener("click",e),s.removeEventListener("click",t)}function t(){const s=document.getElementsByClassName("modal-open")[0],l=s.getElementsByClassName("close")[0];s.classList.remove("modal-open"),l.removeEventListener("click",e),s.removeEventListener("click",t)}function s(e){setTimeout((()=>{const t=document.getElementsByClassName(e);let s=0;for(let e=0;e<t.length;e++)t[e].style.height="";for(let e=0;e<t.length;e++){const l=t[e].offsetHeight;s=l>s?l:s}for(let e=0;e<t.length;e++)t[e].style.height=`${s}px`}),100)}document.addEventListener("DOMContentLoaded",(l=>{s("leader-picture"),s("leader-name"),function(s){const l=document.querySelectorAll(`.${s}`),n=JSON.parse(document.getElementsByClassName(`${s}s-list`)[0].dataset.labels);document.querySelectorAll(".modal-content").forEach((e=>{e.addEventListener("click",(e=>e.stopPropagation()))})),l.forEach((l=>{l.addEventListener("click",(function(){!function(s,l,n){const a=document.getElementsByClassName(s)[0],d=a.getElementsByClassName("image")[0],m=a.getElementsByClassName("information")[0],o=a.getElementsByClassName("close")[0];n.member_image&&(d.innerHTML='<img src="'+n.member_image.url+'" alt="'+n.name.replaceAll("u0022",'"')+'" />');let i='<div class="name">'+n.name.replaceAll("u0022",'"')+"</div>";(n.role||n.phone||n.email||n.age||n.dynamic_fields&&n.dynamic_fields.length)&&(i+="<table>",n.role&&(i+=`<tr><th>${l.role}</th><td>${n.role.replaceAll("u0022",'"')}</td></tr>`),n.age&&(i+=`<tr><th>${l.age}</th><td>${n.age}</td></tr>`),n.email&&(i+=`<tr><th>${l.email}</th><td><a href="mailto:${n.email}">${n.email}</a></td></tr>`),n.phone&&(i+=`<tr><th>${l.phone}</th><td><a href="tel:${n.phone}">${n.phone}</a></td></tr>`),n.dynamic_fields&&n.dynamic_fields.length&&n.dynamic_fields.forEach((e=>{i+=`<tr><th>${e.name}</th><td>${e.value.replaceAll("u0022",'"')}</td></tr>`})),i+="</table>"),m.innerHTML=i,a.classList.add("modal-open"),a.addEventListener("click",t),o.addEventListener("click",e)}(`${s}-modal`,n,JSON.parse(this.dataset[`${s}`]))}))}))}("leader"),function(e){const t=document.getElementsByClassName(`${e}-show-more`)[0];t.addEventListener("click",(function(){document.getElementsByClassName(`${e}s-list`)[0].getElementsByClassName("extended-list")[0].classList.remove("hidden"),t.classList.add("hidden"),document.getElementsByClassName(`${e}-show-less`)[0].classList.remove("hidden"),s(e)}))}("leader"),function(e){const t=document.getElementsByClassName(`${e}-show-less`)[0];t.addEventListener("click",(function(){document.getElementsByClassName(`${e}s-list`)[0].getElementsByClassName("extended-list")[0].classList.add("hidden"),t.classList.add("hidden"),document.getElementsByClassName(`${e}-show-more`)[0].classList.remove("hidden"),s(e)}))}("leader")})),window.addEventListener("resize",(e=>{s("leader-picture"),s("leader-name")}))})();