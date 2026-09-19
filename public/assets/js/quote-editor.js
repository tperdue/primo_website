(() => {
  const editor = document.querySelector('[data-quote-editor]');
  if (!editor) return;

  const items = editor.querySelector('[data-line-items]');
  const template = editor.querySelector('[data-line-template]');
  let nextIndex = items.querySelectorAll('[data-line-item]').length;
  const number = (value) => Number.parseFloat(value) || 0;
  const money = (value) => Math.max(0, value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function recalculate() {
    let subtotal = 0;
    items.querySelectorAll('[data-line-item]').forEach((row) => {
      const total = number(row.querySelector('[data-quantity]').value) * number(row.querySelector('[data-unit-price]').value);
      subtotal += total;
      row.querySelector('[data-line-total]').textContent = money(total);
    });
    const discount = Math.min(number(editor.querySelector('[data-discount]').value), subtotal);
    const tax = Math.max(0, subtotal - discount) * (number(editor.querySelector('[data-tax]').value) / 100);
    const total = Math.max(0, subtotal - discount + tax);
    const deposit = total * (number(editor.querySelector('[data-deposit]').value) / 100);
    editor.querySelector('[data-subtotal]').textContent = money(subtotal);
    editor.querySelector('[data-discount-total]').textContent = `-${money(discount)}`;
    editor.querySelector('[data-tax-total]').textContent = money(tax);
    editor.querySelector('[data-total]').textContent = money(total);
    editor.querySelector('[data-deposit-total]').textContent = money(deposit);
  }

  editor.addEventListener('input', recalculate);
  editor.addEventListener('click', (event) => {
    const add = event.target.closest('[data-add-line]');
    if (add && items.querySelectorAll('[data-line-item]').length < 50) {
      items.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
      recalculate();
      return;
    }
    const remove = event.target.closest('[data-remove-line]');
    if (remove && items.querySelectorAll('[data-line-item]').length > 1) {
      remove.closest('[data-line-item]').remove();
      recalculate();
    }
  });

  recalculate();
})();
