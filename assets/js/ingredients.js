/**
 * BaryoBites - Dynamic Ingredient Row Handler
 * Allows home cooks to add and remove ingredient input rows.
 */
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('ingredients-container');
  const btnAdd = document.getElementById('btn-add-ingredient');

  if (!container || !btnAdd) return;

  // Add Row
  btnAdd.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'ingredient-row';
    row.innerHTML = `
      <input type="text" name="ingredients[]" class="form-control" placeholder="e.g., Next ingredient (amount and name)">
      <button type="button" class="btn-remove-row" title="Remove ingredient" aria-label="Remove">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    `;
    container.appendChild(row);
    // Focus new input
    const input = row.querySelector('input');
    if (input) input.focus();
  });

  // Remove Row Delegation
  container.addEventListener('click', (e) => {
    const btnRemove = e.target.closest('.btn-remove-row');
    if (!btnRemove) return;

    const rows = container.querySelectorAll('.ingredient-row');
    if (rows.length > 1) {
      btnRemove.closest('.ingredient-row').remove();
    } else {
      alert('At least one ingredient row is required in the recipe!');
    }
  });
});
