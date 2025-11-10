let particlesInstance = null;

function initParticles(theme) {
    // Destruir partículas existentes si las hay
    if (particlesInstance) {
        particlesInstance.destroy();
    }

    particlesInstance = particlesJS("particles-js", {
        particles: {
            number: {
                value: 80, // Más partículas para un efecto más denso
                density: {
                    enable: true,
                    value_area: 800
                }
            },
            color: {
                value: theme === "dark" ? "#e5e7eb" : "#1b1e34" // Color según el tema
            },
            shape: {
                type: "circle", // Usar círculos en lugar de polígonos
                stroke: {
                    width: 0,
                    color: "#000000"
                },
                polygon: {
                    nb_sides: 5
                }
            },
            opacity: {
                value: 0.5, // Opacidad más alta para mejor visibilidad
                random: false,
                anim: {
                    enable: false,
                    speed: 1,
                    opacity_min: 0.1,
                    sync: false
                }
            },
            size: {
                value: 3, // Tamaño más pequeño para un aspecto más limpio
                random: true,
                anim: {
                    enable: false,
                    speed: 40,
                    size_min: 0.1,
                    sync: false
                }
            },
            line_linked: {
                enable: true, // Habilitar líneas entre partículas
                distance: 150,
                color: theme === "dark" ? "#e5e7eb" : "#1b1e34", // Color de las líneas
                opacity: 0.4,
                width: 1
            },
            move: {
                enable: true,
                speed: 3, // Movimiento más lento
                direction: "none",
                random: false,
                straight: false,
                out_mode: "bounce",
                bounce: false,
                attract: {
                    enable: false,
                    rotateX: 600,
                    rotateY: 1200
                }
            }
        },
        interactivity: {
            detect_on: "canvas",
            events: {
                onhover: {
                    enable: true,
                    mode: "bubble" // Efecto de burbuja al pasar el mouse
                },
                onclick: {
                    enable: true,
                    mode: "push" // Añadir partículas al hacer clic
                },
                resize: true
            },
            modes: {
                bubble: {
                    distance: 200,
                    size: 6,
                    duration: 2,
                    opacity: 0.8,
                    speed: 3
                },
                push: {
                    particles_nb: 4
                }
            }
        },
        retina_detect: true
    });
}

// Inicializar partículas con el tema actual
const savedTheme = localStorage.getItem('theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
initParticles(savedTheme);

// Función para cambiar el color de las partículas al alternar el modo claro/oscuro
function toggleDarkMode() {
    const htmlElement = document.documentElement;
    const isDarkMode = htmlElement.classList.contains('dark');
    if (isDarkMode) {
        htmlElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    } else {
        htmlElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    }
    // Recargar partículas con el nuevo tema
    initParticles(isDarkMode ? 'light' : 'dark');
}