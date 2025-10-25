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
    $prompt = " Eres un chatbot llamado Repuestin, especializado en ayudar a las personas con una variedad de preguntas. Recuerda presentarte una sola vez con el cliente, ya que es molesto presentarse muchas veces seguidas. Con una vez al principio es suficiente. Puedes responder sobre repuestos automotrices, pero también puedes hablar sobre otros temas como tecnología, entretenimiento, consejos de vida, etc. Aquí tienes una lista de productos disponibles: $productosFormateados Si no sabes la respuesta a una pregunta, puedes decir que no estás seguro y sugerir que busquen más información. ";
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
        body {
            background-color: #f9fafb; /* Color de fondo predeterminado */
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
            bottom: -100px; /* Ajusta la posición vertical inicial de la imagen */
            right: 20px; /* Ajusta la posición horizontal de la imagen */
            transition: bottom 0.5s ease-in-out; /* Transición para la animación */
            z-index: -1; /* Asegúrate de que esté detrás del chat */
        }

        .emoji-container.show {
            bottom: 20px; /* Posición final de la imagen */
        }

        .custom-shadow {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Sombra personalizada */
        }

        /* Estilo para el chatbox sin borde */
        .chat-container {
            display: flex; /* Usar flexbox para alinear los elementos */
            flex-direction: column; /* Alinear los elementos en columna */
            position: fixed; /* Posición fija */
            bottom: 80px; /* Espaciado desde la parte inferior para no chocar con el footer */
            right: 80px; /* Espaciado desde la derecha */
            width: 450px; /* Ancho de la caja del chat */
            height: 500px; /* Altura máxima de la caja del chat */
            background-color: white; /* Color de fondo */
            border-radius: 8px; /* Bordes redondeados */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); /* Sombra */
            z-index: 1000; /* Asegúrate de que esté por encima de otros elementos */
            display: none; /* Ocultar por defecto */
        }

        #chatbox {
            flex-grow: 1; /* Permitir que el contenedor de mensajes crezca */
            overflow-y: auto; /* Permitir desplazamiento vertical */
            margin-bottom: 10px; /* Espaciado inferior para el último mensaje */
        }

        .input-container {
            display: flex; /* Usar flexbox para alinear el input y el botón */
            padding: 10px;
            position: relative; /* Mantener en la parte inferior */
        }

        .input-container input {
            flex: 1; /* Hacer que el input ocupe el espacio disponible */
            margin-right: 10px; /* Espaciado entre el input y el botón */
        }

        .send-button {
            width: 40px; /* Ancho del botón */
            height: 40px; /* Alto del botón */
            border-radius: 50%; /* Hacer el botón circular */
            display: flex; /* Usar flexbox para centrar el ícono */
            align-items: center; /* Centrar verticalmente */
            justify-content: center; /* Centrar horizontalmente */
            background-color: #2563eb; /* Color de fondo del botón */
            color: white; /* Color del ícono */
            border: none; /* Sin borde */
            cursor: pointer; /* Cambiar el cursor al pasar sobre el botón */
            transition: background-color 0.3s; /* Transición suave para el color de fondo */
        }

        .send-button:hover {
            background-color: #3b82f6; /* Color de fondo al pasar el mouse */
        }

        .resizer {
            width: 10px; /* Ancho del controlador de tamaño */
            cursor: ew-resize; /* Cursor de redimensionamiento horizontal */
            position: absolute; /* Posición absoluta */
            right: 0; /* Alinear a la derecha */
            top: 0; /* Alinear a la parte superior */
            height: 100%; /* Altura completa de la caja del chat */
            z-index: 1001; /* Asegúrate de que esté por encima de otros elementos */
        }

        .chat-button {
            position: fixed; /* Posición fija */
            bottom: 10px; /* Espaciado desde la parte inferior */
            right: 10px; /* Espaciado desde la derecha */
            width: 60px; /* Ancho del botón */
            height: 60px; /* Alto del botón */
            border-radius: 50%; /* Hacer el botón circular */
            background-color: #2563eb; /* Color de fondo del botón */
            color: white; /* Color del ícono */
            display: flex; /* Usar flexbox para centrar el ícono */
            align-items: center; /* Centrar verticalmente */
            justify-content: center; /* Centrar horizontalmente */
            cursor: pointer; /* Cambiar el cursor al pasar sobre el botón */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Sombra */
            z-index: 1000; /* Asegúrate de que esté por encima de otros elementos */
            padding: 10px; /* Padding de 10px */
            transition: background-color 0.3s; /* Transición suave para el color de fondo */
        }

        .chat-button:hover {
            background-color: #3b82f6; /* Color de fondo al pasar el mouse */
        }
    </style>
</head>
<body>
    <nav class="bg-custom-blue/95 backdrop-blur-sm dark:bg-gray-800/95 text-white px-6 py-4 flex justify-between items-center fixed w-full top-0 z-50 shadow-lg">
        <div>
            <span class="text-xl">Chatbot - Repuestin</span>
        </div>
        <button onclick="toggleDarkMode()" class="p-2 rounded-full bg-gray-700 dark:bg-gray-600 hover:bg-gray-600 dark:hover:bg-gray-700 transition-colors duration-200">
            <span class="dark:hidden">🌙</span>
            <span class="hidden dark:inline">☀️</span>
        </button>
    </nav>
    
    <div class="chat-container" id="chatContainer"> <!-- Caja del chat -->
        <div class="emoji-container" id="emoji-container">
            <img src="./repuestinbot.png" alt="Imagen de chat" class="emoji-image"> <!-- Reemplaza con la URL de tu imagen -->
        </div>
        <div id="chatbox" class="flex flex-col space-y-2 overflow-y-auto h-80 mb-4 p-4"> <!-- Contenedor de mensajes -->
            <!-- Mensajes del chatbot -->
        </div>
        <div class="input-container"> <!-- Contenedor para el input y el botón -->
            <input type="text" id="userInput" placeholder="Escribe tu mensaje..." class="border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-custom-blue dark:focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            <button id="sendButton" class="send-button">
                <!-- Ícono de enviar (SVG) -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l18-9-9 18-3-6 6-3-18 9z" />
                </svg>
            </button>
        </div>
        <div class="resizer" id="resizer"></div> <!-- Controlador de tamaño -->
    </div>

    <button class="chat-button" id="chatButton">💬</button> <!-- Botón de chat -->

    <footer class="bg-custom-blue/95 dark:bg-gray-800/95 backdrop-blur-sm text-white text-center py-4 fixed bottom-0 w-full text-sm sm:text-base shadow-lg">
        <p>&copy; 2025 Autorepuestos Johbri, C.A. - Todos los derechos reservados</p>
    </footer>

    <script>
        const chatbox = document.getElementById('chatbox');
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');
        const emojiContainer = document.getElementById('emoji-container');
        const resizer = document.getElementById('resizer');
        const chatContainer = document.getElementById('chatContainer');
        const chatButton = document.getElementById('chatButton');

        // Función para mostrar/ocultar el chat
        chatButton.addEventListener('click', () => {
            chatContainer.style.display = chatContainer.style.display === 'none' || chatContainer.style.display === '' ? 'flex' : 'none';
            if (chatContainer.style.display === 'flex') {
                chatContainer.style.right = '80px'; // Mover el chat a la izquierda del botón
                emojiContainer.classList.add('show'); // Mostrar la imagen
            } else {
                chatContainer.style.right = '10px'; // Volver a la posición original
                emojiContainer.classList.remove('show'); // Ocultar la imagen
            }
        });

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

        // Funcionalidad para redimensionar la caja del chat
        resizer.addEventListener('mousedown', (e) => {
            e.preventDefault();
            document.addEventListener('mousemove', resizeChat);
            document.addEventListener('mouseup', stopResize);
        });

        function resizeChat(e) {
            const newHeight = e.clientY - chatContainer.getBoundingClientRect().top;
            if (newHeight > 200 && newHeight < 600) { // Limitar la altura entre 200px y 600px
                chatContainer.style.height = newHeight + 'px';
            }
        }

        function stopResize() {
            document.removeEventListener('mousemove', resizeChat);
            document.removeEventListener('mouseup', stopResize);
        }
    </script>
</body>
</html>