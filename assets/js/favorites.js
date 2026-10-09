/**
 * BaryoBites - Zero-Reload Favorite Toggle
 * Toggles favorite state via client UI & asynchronous fetch.
 */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-favorite').forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();

      const recipeId = btn.getAttribute('data-recipe-id');
      if (!recipeId) return;

      try {
        const response = await fetch('api/favorite-toggle.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ recipe_id: parseInt(recipeId, 10) })
        });

        if (response.status === 401) {
          window.location.href = 'login.php';
          return;
        }

        const data = await response.json();
        if (data.success) {
          const isFav = data.is_favorite;
          if (isFav) {
            btn.classList.add('active');
            btn.setAttribute('title', 'Remove from Favorites');
          } else {
            btn.classList.remove('active');
            btn.setAttribute('title', 'Save to Favorites');
          }

          const svg = btn.querySelector('svg');
          if (svg) {
            svg.setAttribute('fill', isFav ? 'currentColor' : 'none');
          }

          const span = btn.querySelector('span');
          if (span) {
            span.textContent = isFav ? 'Favorited' : 'Favorite';
          }

          // If on favorites.php, optionally fade out and remove card if unfavorited
          if (window.location.pathname.includes('favorites.php') && !isFav) {
            const card = btn.closest('.recipe-card');
            if (card) {
              card.style.opacity = '0';
              card.style.transition = 'opacity 0.25s ease';
              setTimeout(() => card.remove(), 250);
            }
          }
        }
      } catch (err) {
        console.error('Error toggling favorite:', err);
      }
    });
  });
});
