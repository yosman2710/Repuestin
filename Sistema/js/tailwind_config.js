// Configuración de Tailwind CSS
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'custom-blue': '#2563eb',
                'custom-blue-light': '#3b82f6'
            }
        }
    }
};

console.log('Tema restaurado:', localStorage.getItem('theme'));

// Restaurar la preferencia del usuario antes de que la página se renderice
(function () {
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
})();

