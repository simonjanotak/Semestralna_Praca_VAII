(function(){
  'use strict';
  function boot(){
    try{
      var el = document.getElementById('app-urls');
      if(!el) return;
      var get = function(name){ return el.getAttribute(name) || ''; };
      // Map data attributes to expected globals
      if(!window.SEARCH_URL) window.SEARCH_URL = get('data-search-url');
      if(!window.COMMENT_URL_LIST) window.COMMENT_URL_LIST = get('data-comment-list');
      if(!window.COMMENT_URL_CREATE) window.COMMENT_URL_CREATE = get('data-comment-create');
      if(!window.COMMENT_URL_DELETE) window.COMMENT_URL_DELETE = get('data-comment-delete');
      if(!window.COMMENT_URL_EDIT) window.COMMENT_URL_EDIT = get('data-comment-edit');
    }catch(e){ /* ignore */ }
  }
  if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();

