(function(){
  'use strict';
  // Read token from meta tag (rendered server-side in head)
  function readToken(){
    try{
      var m = document.querySelector('meta[name="csrf-token"]');
      return m ? (m.getAttribute('content') || '') : '';
    }catch(e){ return ''; }
  }

  function ensureCsrfInputs(token){
    if(!token) return;
    try{
      var forms = document.querySelectorAll('form[method]');
      Array.prototype.forEach.call(forms, function(f){
        var m = (f.getAttribute('method')||'').toLowerCase();
        if(m !== 'post') return;
        if(f.querySelector('input[name="csrf_token"]')) return;
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'csrf_token';
        inp.value = token;
        f.appendChild(inp);
      });
    }catch(e){/* ignore */}
  }

  function init(){
    var token = readToken();
    try{ window.CSRF_TOKEN = token || window.CSRF_TOKEN || ''; }catch(e){}
    // Ensure forms after DOM ready
    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', function(){ ensureCsrfInputs(window.CSRF_TOKEN); }, false);
    } else {
      ensureCsrfInputs(window.CSRF_TOKEN);
    }
  }

  // init immediately
  init();

  // Expose a small helper in case other scripts want to read current token
  try{ if(!window.getCsrfToken) window.getCsrfToken = function(){ return window.CSRF_TOKEN || readToken() || ''; }; }catch(e){}
})();

