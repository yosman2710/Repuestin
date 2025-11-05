<?php
// Iniciar la sesión para almacenar el historial de la conversación
session_start();

// --- ⚠️ CONFIGURACIÓN DE LA BASE DE DATOS (MySQLi) ---
$servername = "localhost";
$username = "root"; // RECUERDA CAMBIAR 'root'
$password = "";     // RECUERDA USAR UNA CONTRASEÑA FUERTE
$dbname = "repuestos_tiramealgo";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    // Manejo de error de conexión más discreto en producción
    die("Conexión fallida: " . $conn->connect_error);
}
// ----------------------------------------------------

// --- FUNCIÓN 1: OBTENER PRODUCTOS ---
function obtenerProductos($conn) {
    $sql = "SELECT nombre_producto, precio_producto, stock_producto FROM productos";
    // Usamos el método query del objeto $conn de mysqli
    $result = $conn->query($sql);
    $productos = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
    }
    // Si la consulta falla, $productos es un array vacío
    return $productos;
}

// --- FUNCIÓN 2: ENVIAR MENSAJE A GEMINI (PROMPT INJECTION) ---
function enviarMensajeAGemini($mensaje, $productos, $historial, $primeraInteraccion) {
    // API Key (Debe ser protegida en producción)
    $apiKey = "AIzaSyBMvGi9_c-_yYO6zQYjD5odXBAfQHjvuLg";
    $model = 'gemini-2.5-flash'; // Usando el modelo potente elegido

    // 1. Formatear los productos para el prompt
    $productosFormateados = "Lista de Productos:\n";
    foreach ($productos as $producto) {
        // Formato claro para el modelo
        $productosFormateados .= "- {$producto['nombre_producto']}: \${$producto['precio_producto']} (Stock: {$producto['stock_producto']})\n";
    }

    // 2. Crear la Instrucción del Sistema/Prompt Maestro
    // Incluye todas las reglas, personalidad y datos de contexto.
    $promptMaestro = "
        Eres un chatbot llamado **Repuestin**, especializado en repuestos automotrices. 
        Tu personalidad es la de un vendedor maracucho: **confiado, ameno y respetuoso** (¡Cuidado con los \"chistes\" no profesionales!). 
        Recuerda **presentarte solo una vez** al inicio de la conversación. 
        Puedes responder sobre repuestos, tecnología, entretenimiento y consejos de vida.

        Aquí tienes la lista de productos disponibles:
        $productosFormateados
        
        --- REGLAS DE NEGOCIO ---
        1. **Presupuestos:** Debes presentarlos de manera ordenada con el siguiente formato estricto:
           Presupuesto # [Número incremental]
           [cantidad de producto], [producto en si]
           [otra cantidad], [otro producto]
           TOTAL: [TOTAL A PAGAR en $]
        
        2. **Delivery Maracaibo (Base):** Calcula un valor aproximado de **$2 por kilómetro**. Debes mencionar una lista de *zonas de referencia* para el cliente.
        
        3. **Delivery San Francisco:** El costo fijo es de **$13**.
        
        4. **Delivery GRATIS:** Si la compra supera los **$200**, el delivery es gratuito para Maracaibo y San Francisco.

        5. **Interacción:** Mantén las respuestas **breves** y usa jerga maracucha si aplica ('mi pana', 'verga', 'coño e la madre' *solo si es muy ameno*). Haz preguntas ocasionales (gustos, carro favorito) pero **prioriza la venta**.

        6. **Profesor Luis Uribe:** Si lo identificas, después de la **cuarta interacción**, refiérete a él por su nombre.

        7. **No disponible:** Si no sabes la respuesta o el repuesto no está en la lista, sé honesto y sugiérele buscar más información o contactar un humano.
        
        --- HISTORIAL DE LA CONVERSACIÓN ---
    ";

    // 3. Agregar el historial de la conversación al Prompt
    $historialTexto = "";
    $interaccionContador = 0;
    foreach ($historial as $interaccion) {
        $historialTexto .= "Usuario dice: {$interaccion['usuario']}\n";
        $historialTexto .= "Repuestín responde: {$interaccion['bot']}\n";
        $interaccionContador++;
    }
    
    // 4. Construir el Prompt final para el modelo
    $promptFinal = $promptMaestro . $historialTexto . "\n\nPregunta del usuario AHORA: $mensaje";

    // 5. Configuración de la API (Usando el formato de 'contents' del modelo más nuevo)
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    
    $data = json_encode([
        // El prompt completo va en el primer (y único) content
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    [
                        "text" => $promptFinal
                    ]
                ]
            ]
        ],
        // Opcionalmente, puedes añadir generationConfig (temperature)
        "generationConfig" => [
            "temperature" => 0.8, // Un poco más creativo para la personalidad
        ]
    ]);
    
    // 6. Realizar la petición HTTP (usando stream_context_create)
    $options = [
        "http" => [
            "header" => "Content-Type: application/json",
            "method" => "POST",
            "content" => $data,
            "timeout" => 30, // Aumentar el tiempo de espera
        ]
    ];
    
    $context = stream_context_create($options);
    
    // Suprimir advertencias si file_get_contents falla, ya que lo manejamos con el resultado
    $result = @file_get_contents($url, false, $context); 
    
    if ($result === FALSE) {
        // Manejo de errores de red o conexión
        return "Lo siento, mi pana. La conexión está fallando. ¡Inténtalo de nuevo!";
    }
    
    $response = json_decode($result, true);

    // Manejo de errores de la API (por ejemplo, API key inválida, bloqueo de contenido)
    if (isset($response['error'])) {
        $error_message = $response['error']['message'] ?? "Error desconocido de la API.";
        error_log("Error de la API de Gemini: " . $error_message);
        return "¡Verga, mi llave! La IA tuvo un percance. Error: " . (isset($response['error']['message']) ? substr($response['error']['message'], 0, 100) . '...' : 'Desconocido');
    }

    // Extraer la respuesta del modelo
    $respuestaBot = $response['candidates'][0]['content']['parts'][0]['text'] ?? "No se pudo obtener una respuesta válida. Intenta de nuevo, pues.";

    // 7. Saludo inicial
    if ($primeraInteraccion) {
        // Dejar que el modelo se presente según el prompt para mantener la personalidad
        return $respuestaBot;
    }
    
    return $respuestaBot;
}

// --- PROCESAMIENTO PRINCIPAL DE LA SOLICITUD ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = htmlspecialchars($_POST["mensaje"]);
    
    $productos = obtenerProductos($conn);
    
    // Inicializar historial
    if (!isset($_SESSION['historial'])) {
        $_SESSION['historial'] = [];
    }
    $historial = $_SESSION['historial'];
    
    $primeraInteraccion = count($historial) === 0;
    
    $respuesta = enviarMensajeAGemini($mensaje, $productos, $historial, $primeraInteraccion);
    
    // Agregar la interacción actual al historial
    $historial[] = [
        "usuario" => $mensaje,
        "bot" => $respuesta
    ];
    
    // Limitar el historial a los últimos 10 mensajes
    if (count($historial) > 10) {
        $historial = array_slice($historial, -10);
    }
    
    $_SESSION['historial'] = $historial;
    
    echo $respuesta;
    exit;
}

// Cerrar conexión (se cierra automáticamente al final del script, pero es buena práctica)
if (isset($conn)) {
    $conn->close();
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
            z-index: -1;
        }

        .typing-indicator-container {
            display: flex;
            align-items: center;
            background-color: #f3f4f6;
            border-radius: 9999px;
            padding: 5px;
            margin-bottom: 0.5rem;
        }

        .dark .typing-indicator-container {
            background-color: #4b5563; /* Gray-600 en dark mode */
        }

        .typing-indicator {
            display: inline-block;
            width: 0.5em; 
            height: 0.5em;
            border-radius: 50%;
            background-color: #2563eb;
            margin: 0 2px;
            animation: blink 0.7s infinite alternate;
        }

        @keyframes blink {
            0% { opacity: 1; }
            100% { opacity: 0.5; }
        }

        .emoji-container {
            position: absolute;
            top: -100px;
            right: 20px;
            transition: transform 3s ease-in-out;
            font-size: 4rem;
        }

        .emoji-container.left {
            transform: translateX(-450%);
        }

        .custom-shadow {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        #chatbox {
            border: none;
            overflow-y: auto;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>
<body class="bg-pattern transition-colors duration-200">
    <div id="particles-js" class="fixed inset-0 z-0"></div>

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
        <div class="bg-white dark:bg-gray-800 p-10 rounded-lg custom-shadow max-w-2xl w-full relative">
            <div class="emoji-container" id="emoji-container">😇</div>
            <div id="chatbox" class="h-80 mb-4 p-4 flex flex-col space-y-2">
                <?php
                    // Recargar el historial al cargar la página (opcional, pero útil)
                    if (isset($_SESSION['historial']) && is_array($_SESSION['historial'])) {
                        foreach ($_SESSION['historial'] as $interaccion) {
                            $clase_usuario = 'bg-blue-500 text-white self-end text-right';
                            $clase_bot = 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white self-start text-left';
                            
                            // Mensaje del usuario
                            echo "<div class='rounded-lg p-2 mb-2 max-w-xs {$clase_usuario}'>{$interaccion['usuario']}</div>";
                            
                            // Respuesta del bot
                            echo "<div class='rounded-lg p-2 mb-2 max-w-xs {$clase_bot}'>{$interaccion['bot']}</div>";
                        }
                    }
                ?>
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
                particlesInstance.particles.color.value = theme === "dark" ? "#e5e7eb" : "#1b1e34";
                particlesInstance.particles.line_linked.color = theme === "dark" ? "#e5e7eb" : "#1b1e34";
                particlesInstance.fn.particlesDraw(); // Forzar redibujo en la instancia existente
            } else {
                particlesInstance = particlesJS("particles-js", {
                    particles: {
                        number: {
                            value: 80,
                            density: {
                                enable: true,
                                value_area: 800
                            }
                        },
                        color: {
                            value: theme === "dark" ? "#e5e7eb" : "#1b1e34"
                        },
                        shape: {
                            type: "circle",
                            stroke: {
                                width: 0,
                                color: "#000000"
                            },
                            polygon: {
                                nb_sides: 5
                            }
                        },
                        opacity: {
                            value: 0.5,
                            random: false,
                            anim: {
                                enable: false,
                                speed: 1,
                                opacity_min: 0.1,
                                sync: false
                            }
                        },
                        size: {
                            value: 3,
                            random: true,
                            anim: {
                                enable: false,
                                speed: 40,
                                size_min: 0.1,
                                sync: false
                            }
                        },
                        line_linked: {
                            enable: true,
                            distance: 150,
                            color: theme === "dark" ? "#e5e7eb" : "#13141bff",
                            opacity: 0.4,
                            width: 1
                        },
                        move: {
                            enable: true,
                            speed: 3,
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
                                mode: "bubble"
                            },
                            onclick: {
                                enable: true,
                                mode: "push"
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
            userMessageElement.textContent = userMessage;
            userMessageElement.classList.add('bg-blue-500', 'text-white', 'rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-end', 'text-right');
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
            chatbox.scrollTop = chatbox.scrollHeight;

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
                if (chatbox.contains(typingIndicator)) {
                    chatbox.removeChild(typingIndicator);
                }

                // Mostrar respuesta del chatbot
                const botMessageElement = document.createElement('div');
                botMessageElement.textContent = response;
                // Clases dinámicas para dark mode
                const botClasses = ['rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-start', 'text-left'];
                if (document.documentElement.classList.contains('dark')) {
                    botClasses.push('bg-gray-700', 'text-white');
                } else {
                    botClasses.push('bg-gray-300', 'text-gray-800');
                }
                botMessageElement.classList.add(...botClasses);
                chatbox.appendChild(botMessageElement);

                // Desplazar hacia abajo
                chatbox.scrollTop = chatbox.scrollHeight;
            })
            .catch(error => {
                console.error("Error al enviar mensaje:", error);
                
                // Si hay error, también quitar el indicador
                if (chatbox.contains(typingIndicator)) {
                    chatbox.removeChild(typingIndicator);
                }

                const errorMessageElement = document.createElement('div');
                errorMessageElement.textContent = "Error: No se pudo conectar con el servidor.";
                errorMessageElement.classList.add('bg-red-500', 'text-white', 'rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-start', 'text-left');
                chatbox.appendChild(errorMessageElement);
                chatbox.scrollTop = chatbox.scrollHeight;
            });

            // Limpiar el input
            userInput.value = '';
            chatbox.scrollTop = chatbox.scrollHeight; // Desplazar hacia abajo
        });
    </script>
</body>
</html>