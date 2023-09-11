const articles = document.getElementById('articles');

if (articles) {
	articles.addEventListener('click', (e) => {
		if (Array.from(e.target.classList).includes('delete-article')) {
			if (confirm('Are you sure?')) {
				const id = e.target.getAttribute('data-id');

				fetch(`/article/delete/${id}`, {
					method: 'DELETE'
				}).then(() => window.location.reload());
			}
		}
	});
}