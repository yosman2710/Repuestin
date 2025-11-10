// Configuración de Tailwind CSS
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                'custom-silverLight': '#F0F0F0', 
                //Fondo general del sitio, mantiene una base limpia y técnica.
                'custom-wineDeep': '#5A0B11', 
                //Fondo de cabecera, banner principal o barra superior.
                'custom-black': '#1C1C1C', 
                //Texto sobre fondos claros (títulos, párrafos principales).
                'custom-gray': '#7D7D7D', 
                //Subtítulos, precios, descripciones, separadores finos.
                'custom-orange': '#D46A2E', 
                //Botones principales (“Comprar”, “Agregar al carrito”).
                'custom-wineDark': '#46080D', 
                //Hover sobre botones, iconos activos o bordes destacados.
                'custom-steelDark': '#2E2E2E', 
                //Fondo del footer, menú inferior o secciones de contraste.
                'custom-silver':'#E1E1E1', 
                //Texto en secciones oscuras (alto contraste y legibilidad).
                'custom-silverTitan': '#969696', 
                //Texto auxiliar, íconos o descripciones menores.
                'custom-red':'#8B1E1E' 
                //Hover sobre botones o enlaces, aporta energía y contraste.
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

