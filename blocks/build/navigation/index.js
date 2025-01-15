(()=>{"use strict";var e,o={947:(e,o,r)=>{const t=window.wp.blocks,n=window.wp.element,s=window.wp.blockEditor,i=window.wp.components,l=window.wp.serverSideRender;var a=r.n(l);const u=window.wp.i18n,c=window.ReactJSXRuntime,p=JSON.parse('{"UU":"myclub-groups/navigation"}');(0,t.registerBlockType)(p.UU,{edit:function({attributes:e,setAttributes:o}){const[r,t]=(0,n.useState)([]),l={label:(0,u.__)("Select a group","myclub-groups"),value:""};return(0,n.useEffect)((()=>{!function(e,o){const{apiFetch:r}=wp;r({path:"/myclub/v1/groups"}).then((r=>{const t=r.map((e=>({label:e.title,value:e.id})));t.unshift(o),e(t)}))}(t,l)}),[]),(0,c.jsxs)(c.Fragment,{children:[(0,c.jsx)(s.InspectorControls,{children:(0,c.jsx)(i.PanelBody,{title:(0,u.__)("Content settings","myclub-groups"),children:(0,c.jsx)(i.PanelRow,{children:r.length?(0,c.jsx)(i.SelectControl,{label:(0,u.__)("Group","myclub-groups"),value:e.post_id,options:r,onChange:e=>{o({post_id:e})}}):(0,c.jsx)(i.Spinner,{})})})}),(0,c.jsx)("div",{...(0,s.useBlockProps)(),children:e.post_id?(0,c.jsx)(a(),{block:"myclub-groups/navigation",attributes:e}):(0,c.jsx)("div",{className:"myclub-groups-navigation",children:(0,c.jsx)("div",{className:"no-group-selected",children:(0,u.__)("No group selected","myclub-groups")})})})]})}})}},r={};function t(e){var n=r[e];if(void 0!==n)return n.exports;var s=r[e]={exports:{}};return o[e](s,s.exports,t),s.exports}t.m=o,e=[],t.O=(o,r,n,s)=>{if(!r){var i=1/0;for(c=0;c<e.length;c++){for(var[r,n,s]=e[c],l=!0,a=0;a<r.length;a++)(!1&s||i>=s)&&Object.keys(t.O).every((e=>t.O[e](r[a])))?r.splice(a--,1):(l=!1,s<i&&(i=s));if(l){e.splice(c--,1);var u=n();void 0!==u&&(o=u)}}return o}s=s||0;for(var c=e.length;c>0&&e[c-1][2]>s;c--)e[c]=e[c-1];e[c]=[r,n,s]},t.n=e=>{var o=e&&e.__esModule?()=>e.default:()=>e;return t.d(o,{a:o}),o},t.d=(e,o)=>{for(var r in o)t.o(o,r)&&!t.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:o[r]})},t.o=(e,o)=>Object.prototype.hasOwnProperty.call(e,o),(()=>{var e={240:0,736:0};t.O.j=o=>0===e[o];var o=(o,r)=>{var n,s,[i,l,a]=r,u=0;if(i.some((o=>0!==e[o]))){for(n in l)t.o(l,n)&&(t.m[n]=l[n]);if(a)var c=a(t)}for(o&&o(r);u<i.length;u++)s=i[u],t.o(e,s)&&e[s]&&e[s][0](),e[s]=0;return t.O(c)},r=globalThis.webpackChunkmyclub_groups=globalThis.webpackChunkmyclub_groups||[];r.forEach(o.bind(null,0)),r.push=o.bind(null,r.push.bind(r))})();var n=t.O(void 0,[736],(()=>t(947)));n=t.O(n)})();