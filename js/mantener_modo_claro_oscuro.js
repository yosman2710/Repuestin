// Función para alternar el modo oscuro/claro
function toggleDarkMode() {
    const htmlElement = document.documentElement;
    const isDarkMode = htmlElement.classList.contains('dark');
    if (isDarkMode) {
        htmlElement.classList.remove('dark');
        localStorage.setItem('theme', 'light'); // Guardar preferencia en localStorage
    } else {
        htmlElement.classList.add('dark');
        localStorage.setItem('theme', 'dark'); // Guardar preferencia en localStorage
    }
}

console.log('Modo alternado:', document.documentElement.classList.contains('dark') ? 'oscuro' : 'claro');