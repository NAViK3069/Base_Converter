document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('.subform');
  const radios = document.querySelectorAll('input[name="tool"]');

  function setDisabledFor(formEl, disabled) {
    formEl.querySelectorAll('input,select,textarea,button').forEach(el=>{
      if (el.type !== 'hidden') el.disabled = disabled;
    });
  }
  function showForm(name) {
    forms.forEach(f=>{
      const active = f.getAttribute('data-form')===name;
      f.toggleAttribute('hidden', !active);
      setDisabledFor(f, !active);
    });
  }
  const checked = document.querySelector('input[name="tool"]:checked');
  showForm(checked ? checked.value : 'convert');
  radios.forEach(r=>r.addEventListener('change', e=>showForm(e.target.value)));

  // Convert placeholder
  const cvFrom = document.getElementById('cv_from');
  const cvInput = document.getElementById('cv_input');
  function updateCvPh(){
    if(!cvFrom||!cvInput) return;
    const b = parseInt(cvFrom.value,10);
    const eg = (b===2)?'101':(b===8)?'765':(b===10)?'123':'2AF';
    cvInput.placeholder = `[ ใส่เลขฐาน ${b} เช่น ${eg} ]`;
  }
  if (cvFrom && cvInput){ cvFrom.addEventListener('change', updateCvPh); updateCvPh(); }

  // Code transform placeholder
  const ctMode = document.getElementById('ct_mode');
  const ctInput = document.getElementById('ct_input');
  function updateCtPh(){
    if(!ctMode||!ctInput) return;
    const map = {
      B2G:'Binary เช่น 1011',
      G2B:'Gray เช่น 1110',
      BCD2DEC:'กลุ่ม BCD เช่น 0011 0101',
      DEC2BCD:'เลขฐาน 10 เช่น 35',
      ASC2BIN:'ข้อความ ASCII เช่น HELLO',
      BIN2ASC:'Binary 8-bit เว้นวรรค เช่น 01001000 01001001'
    };
    ctInput.placeholder = `[ ${map[ctMode.value]} ]`;
  }
  if (ctMode&&ctInput){ ctMode.addEventListener('change', updateCtPh); updateCtPh(); }
});
