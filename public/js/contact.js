(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const list = document.getElementById('departments-list');
    const box = document.getElementById('department-contact');
    const text = document.getElementById('department-contact-text');
    if(!list || !text) return;

    list.addEventListener('click', function(e){
      const btn = e.target.closest('button');
      if(!btn) return;
      const name = btn.textContent.trim();
      const email = btn.dataset.contact || '';
      const phone = btn.dataset.phone || '';
      const parts = [name];
      if(email) parts.push(email);
      if(phone) parts.push(phone);
      text.textContent = parts.join(' • ');
      if(box) box.style.display = '';
    });
  });
})();
