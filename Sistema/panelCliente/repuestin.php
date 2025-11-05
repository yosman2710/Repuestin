<?php
// Iniciar la sesión para almacenar el historial de la conversación
session_start();

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "repuestos";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Función para obtener productos de la base de datos
function obtenerProductos($conn) {
    $sql = "SELECT nombre_producto, precio_producto, stock_producto FROM productos";
    $result = $conn->query($sql);
    $productos = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }
    return $productos;
}

// Función para enviar un mensaje a la API de Gemini
function enviarMensajeAGemini($mensaje, $productos, $historial, $primeraInteraccion) {
    $apiKey = "AIzaSyBMvGi9_c-_yYO6zQYjD5odXBAfQHjvuLg"; // Recuerda proteger tu API Key
    // Formatear los productos para el prompt
    $productosFormateados = "";
    foreach ($productos as $producto) {
        $productosFormateados .= "- {$producto['nombre_producto']}: \${$producto['precio_producto']} (Stock: {$producto['stock_producto']})\n";
    }
    // Crear el prompt para el chatbot
    $prompt = " Eres un chatbot llamado Repuestin, especializado en ayudar a las personas con una variedad de preguntas. Tu personalidad es la de un vendedor maracucho, confiado pero respetuoso. Recuerda presentarte solo una vez al inicio de la conversación, ya que es molesto hacerlo repetidamente. Puedes responder sobre repuestos automotrices y otros temas como tecnología, entretenimiento y consejos de vida.

Aquí tienes una lista de productos disponibles: $productosFormateados.

Cuando un cliente solicite un presupuesto, preséntalo de manera ordenada en el siguiente formato:
Presupuesto # (Número de presupuesto)
[cantidad de producto], [producto en si]. (Todo en una lista, uno debajo del otro, según la cantidad de productos solicitados) TOTAL: (TOTAL A PAGAR)
Si te preguntan sobre el precio del delivery a alguna zona de Maracaibo, proporciona una lista de referencias de zonas y calcula un valor aproximado de $2 por kilómetro. Para San Francisco, el costo es de $13. Recuerda mencionar que si la compra supera los $200, el delivery es gratuito tanto para Maracaibo como para San Francisco.

Además, interactúa de manera amena con los clientes, haciendo chistes y jugando un poco, como lo haría un vendedor. Mantén tus respuestas breves para evitar que el cliente se aburra con mucho texto.

Cuando el profesor Luis Uribe interactúe contigo, sorpréndelo después de cuatro mensajes, refiriéndote a él por su nombre. Luego, hazle preguntas ocasionales sobre sus gustos y preferencias en marcas de repuestos y su carro favorito, pero recuerda que el objetivo principal es vender.

Si no sabes la respuesta a una pregunta, sé honesto y sugiere que busquen más información.";
    // Agregar el historial de la conversación al prompt
    if (!empty($historial)) {
        $prompt .= "\n\nHistorial de la conversación:\n";
        foreach ($historial as $interaccion) {
            $prompt .= "- {$interaccion['usuario']}\n";
            $prompt .= "- {$interaccion['bot']}\n";
        }
    }
    // Agregar el mensaje actual del usuario
    $prompt .= "\n\nPregunta del usuario: $mensaje";
    // Enviar mensaje a la API de Gemini
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=$apiKey";
    $data = json_encode([
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => $prompt
                    ]
                ]
            ]
        ]
    ]);
    $options = [
        "http" => [
            "header" => "Content-Type: application/json",
            "method" => "POST",
            "content" => $data
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        return "Lo siento, hubo un error al procesar tu solicitud. Por favor, inténtalo de nuevo.";
    }
    $response = json_decode($result, true);
    // Si es la primera interacción, agregar el saludo
    if ($primeraInteraccion) {
        return "¡Hola! Repuestin a la orden, mi pana. " . $response['candidates'][0]['content']['parts'][0]['text'];
    }
    return $response['candidates'][0]['content']['parts'][0]['text'] ?? "No se pudo obtener una respuesta.";
}

// Procesar el mensaje del usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = htmlspecialchars($_POST["mensaje"]);
    // Obtener productos de la base de datos
    $productos = obtenerProductos($conn);
    // Obtener el historial de la conversación desde la sesión
    if (!isset($_SESSION['historial'])) {
        $_SESSION['historial'] = [];
    }
    $historial = $_SESSION['historial'];
    // Determinar si es la primera interacción
    $primeraInteraccion = count($historial) === 0;
    // Enviar mensaje a la API de Gemini
    $respuesta = enviarMensajeAGemini($mensaje, $productos, $historial, $primeraInteraccion);
    // Agregar la interacción actual al historial
    $historial[] = [
        "usuario" => $mensaje,
        "bot" => $respuesta
    ];
    // Limitar el historial a los últimos 10 mensajes (5 interacciones)
    if (count($historial) > 10) {
        $historial = array_slice($historial, -10);
    }
    // Guardar el historial actualizado en la sesión
    $_SESSION['historial'] = $historial;
    // Devolver la respuesta al frontend
    echo $respuesta;
    exit;
}
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repuestin</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script>
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
        }
    </script>
    <style>
        .bg-pattern {
            background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0.85) 100%);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .dark .bg-pattern {
            background-image: linear-gradient(to bottom, rgba(17, 24, 39, 0.97) 0%, rgba(17, 24, 39, 0.97) 100%);
        }

        /* Estilo para el contenedor de partículas */
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1; /* Asegúrate de que esté detrás de otros elementos */
        }

        .typing-indicator-container {
            display: flex;
            align-items: center;
            background-color: #f3f4f6; /* Color de fondo de la burbuja */
            border-radius: 9999px; /* Para hacerla completamente redonda */
            padding: 5px; /* Espaciado interno */
            margin-bottom: 0.5rem; /* Espaciado inferior */
        }

        .typing-indicator {
            display: inline-block;
            width: 0.5em; 
            height: 0.5em; /* Tamaño más pequeño */
            border-radius: 50%;
            background-color: #2563eb; /* Color de los puntos */
            margin: 0 2px; /* Espaciado entre los puntos */
            animation: blink 0.7s infinite alternate;
        }

        @keyframes blink {
            0% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        .emoji-container {
            position: absolute;
            top: -100px; /* Ajusta la posición vertical del emoji */
            right: 20px; /* Ajusta la posición horizontal del emoji */
            transition: transform 3s ease-in-out; /* Transición para el movimiento */
            font-size: 4rem; /* Tamaño del emoji, puedes modificar aquí */
        }

        .emoji-container.left {
            transform: translateX(-450%); /* Mueve el emoji a la izquierda */
        }

        .custom-shadow {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Sombra personalizada */
        }

        /* Estilo para el chatbox sin borde */
        #chatbox {
            border: none; /* Eliminar el borde */
            overflow-y: auto; /* Permitir el desplazamiento vertical */
        }
    </style>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body class="bg-pattern transition-colors duration-200">
    <!-- Contenedor para Particles.js -->
    <div id="particles-js" class="fixed inset-0 z-0"></div>

    <!-- Navbar -->
    <nav class="bg-custom-blue dark:bg-gray-800 text-white px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
        <a href="cliente.php"
                class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span class="text-sm">Volver</span>
            </a>

            <div class="text-xl font-bold">Chatbot - Repuestin</div>
            <div class="flex items-center gap-4">
                <button
                    onclick="toggleDarkMode()"
                    class="p-2 rounded-full bg-gray-700 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 transition-colors duration-200"
                    aria-label="Alternar entre modo oscuro y claro"
                 >
                    <span class="dark:hidden">🌙</span>
                    <span class="hidden dark:inline">☀️</span>
                </button>
                <a href="../logica/cerrar-sesion.php" class="hover:underline">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen flex flex-col items-center justify-center px-4 pt-16">
        <div class="bg-white dark:bg-gray-800 p-10 rounded-lg custom-shadow max-w-2xl w-full relative"> <!-- Aumentar el ancho -->
            <div class="emoji-container" id="emoji-container">😇</div>
            <div id="chatbox" class="h-80 mb-4 p-4 flex flex-col space-y-2"> <!-- Eliminar borde y ajustar altura -->
                <!-- Mensajes del chatbot -->
            </div>
            <input type="text" id="userInput" placeholder="Escribe tu mensaje..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-custom-blue dark:focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            <button id="sendButton" class="w-full bg-custom-blue hover:bg-custom-blue-light dark:bg-blue-600 dark:hover:bg-blue-700 text-white py-2 px-4 rounded-md transition-colors duration-200 font-semibold mt-2">
                Enviar
            </button>
        </div>
    </main>

    <footer class="bg-custom-blue/95 dark:bg-gray-800/95 backdrop-blur-sm text-white text-center py-4 fixed bottom-0 w-full text-sm sm:text-base shadow-lg">
        <p>&copy; 2025 Autorepuestos, C.A. - Todos los derechos reservados</p>
    </footer>

    <script>
        let particlesInstance = null;

        function initParticles(theme) {
            if (particlesInstance) {
                // Cambiar el color de las partículas sin reinicializar
                particlesInstance.particles.color.value = theme === "dark" ? "#e5e7eb" : "#1b1e34";
                particlesInstance.particles.line_linked.color = theme === "dark" ? "#e5e7eb" : "#1b1e34";
            } else {
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
        }
    </script>
    <script>
        // Mantener el tema al recargar la página
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', savedTheme === 'dark');

        // Inicializar partículas con el tema actual
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
            // Cambiar el color de las partículas sin reinicializar
            initParticles(isDarkMode ? 'light' : 'dark');
        }

        const chatbox = document.getElementById('chatbox');
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        const emojiContainer = document.getElementById('emoji-container');

        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendButton.click();
            }
        });

        sendButton.addEventListener('click', () => {
            const userMessage = userInput.value;
            if (userMessage.trim() === '') return;

            // Mostrar mensaje del usuario
            const userMessageElement = document.createElement('div');
            userMessageElement.textContent = userMessage; // Eliminar "Tú:"
            userMessageElement.classList.add('bg-blue-500', 'text-white', 'rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-end', 'text-right'); // Burbuja del usuario
            chatbox.appendChild(userMessageElement);

            // Animación del emoji
            emojiContainer.classList.add('left');
            emojiContainer.textContent = '😈';
            setTimeout(() => {
                emojiContainer.classList.remove('left');
                emojiContainer.textContent = '😇';
            }, 3000);

            // Mostrar indicador de escritura del bot
            const typingIndicator = document.createElement('div');
            typingIndicator.classList.add('typing-indicator-container', 'self-start');
            typingIndicator.innerHTML = `
                <div class="typing-indicator"></div>
                <div class="typing-indicator"></div>
                <div class="typing-indicator"></div>
            `;
            chatbox.appendChild(typingIndicator);

            // Enviar mensaje al backend
            fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `mensaje=${encodeURIComponent(userMessage)}`,
            })
            .then(response => response.text())
            .then(response => {
                // Eliminar el indicador de escritura
                chatbox.removeChild(typingIndicator);

                // Mostrar respuesta del chatbot
                const botMessageElement = document.createElement('div');
                botMessageElement.textContent = response; // Eliminar "Bot:"
                botMessageElement.classList.add('bg-gray-300', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-white', 'rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-start', 'text-left'); // Burbuja del bot
                chatbox.appendChild(botMessageElement);

                // Desplazar hacia abajo
                chatbox.scrollTop = chatbox.scrollHeight;
            })
            .catch(error => {
                console.error("Error al enviar mensaje:", error);
            });

            // Limpiar el input
            userInput.value = '';
            chatbox.scrollTop = chatbox.scrollHeight; // Desplazar hacia abajo
        });
    </script>
</body>
</html>