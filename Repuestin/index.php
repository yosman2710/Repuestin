<?php

session_start();


$servername = "localhost";
$username = "root"; // ⚠️ RECUERDA CAMBIAR 'root' por un usuario con menos permisos
$password = "";     // ⚠️ RECUERDA USAR UNA CONTRASEÑA FUERTE
$dbname = "repuestos";

// Crear conexión usando PDO (Recomendado sobre mysqli)
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configurar PDO para lanzar excepciones en caso de error (Mejor manejo de errores)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Conexión fallida (PDO): " . $e->getMessage());
}

// --- FUNCIÓN 1: OBTENER PRODUCTOS (RESTABLECIDA Y NECESARIA) ---
function obtenerProductos($conn) {
    $sql = "SELECT nombre_producto, precio_producto, stock_producto FROM productos";
    try {
        $stmt = $conn->query($sql);
        // fetchAll es el equivalente a tu bucle while
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener productos: " . $e->getMessage());
        return [];
    }
    return $productos;
}

// --- FUNCIÓN 2: ENVIAR MENSAJE A GEMINI (CORREGIDA Y FINAL) ---
// --- FUNCIÓN 2: ENVIAR MENSAJE A GEMINI (CORRECCIÓN FINAL) ---
function enviarMensajeAGemini($mensaje, $productos, $historial, $primeraInteraccion) {
    
    // ... (Configuración de API Key, URL y productosFormateados - SIN CAMBIOS) ...
    $apiKey = "AIzaSyBMvGi9_c-_yYO6zQYjD5odXBAfQHjvuLg"; 
    $model = 'gemini-2.5-flash';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $productosFormateados = "";
    // ... (Lógica para construir $productosFormateados) ...

    // 3. Estructura de 'contents' con System Instruction como PRIMER elemento.
    $contents = [];
    $systemInstructionText = "Eres un chatbot llamado Repuestin, especializado en ayudar a personas sobre repuestos automotrices. Usa la siguiente lista de productos como contexto para responder. Si te preguntan por un repuesto que no está en la lista, sé honesto e indica que no lo tienes. Productos disponibles:\n$productosFormateados";
    
    // ✅ CORRECCIÓN 1: Añadir la instrucción del sistema con el rol "user" o "model"
    //    Si la API es estricta y solo acepta 'user'/'model' en el historial:
    
    // Vamos a intentar con 'user' como primera instrucción:
    $contents[] = [
        "role" => "user", 
        "parts" => [["text" => "Establece tu personalidad: $systemInstructionText"]]
    ];

    // Si la corrección anterior falla, la ÚNICA forma de pasar la instrucción es esta:
    /*
    $contents[] = [
        "role" => "system", // ¡Usar el rol 'system' AQUI!
        "parts" => [["text" => $systemInstructionText]]
    ];
    */

    // Agregar el historial de la conversación (SIN CAMBIOS)
    foreach ($historial as $interaccion) {
        $contents[] = ["role" => "user", "parts" => [["text" => $interaccion['usuario']]]];
        $contents[] = ["role" => "model", "parts" => [["text" => $interaccion['bot']]]];
    }
    
    // Agregar el mensaje actual del usuario (SIN CAMBIOS)
    $contents[] = ["role" => "user", "parts" => [["text" => $mensaje]]];
    
    // 4. Petición POST (¡Simplificada para evitar errores de nombres de campos!)
    $data = json_encode([
        "contents" => $contents,
        
        // ✅ CORRECCIÓN 2: Dejamos SOLO la temperatura.
        "generationConfig" => [ 
            "temperature" => 0.7, 
        ]
    ]);
    // 5. Llamada con cURL y manejo de errores (sin cambios)
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    $error_num = curl_errno($ch);
    $error_msg = curl_error($ch);
    
    if ($error_num) {
        curl_close($ch);
        return "❌ Error de Conexión cURL ({$error_num}): " . $error_msg;
    }
    curl_close($ch);

    $json = json_decode($response, true);
    
    if (isset($json['error'])) {
        $error_message = $json['error']['message'] ?? "Error desconocido de la API.";
        return "🚨 Error de la API: " . $error_message;
    }
    
    $respuestaBot = $json['candidates'][0]['content']['parts'][0]['text'] ?? "Sin respuesta. El JSON devuelto no tiene 'candidates'.";

    if ($primeraInteraccion) {
        return "¡Hola! Repuestin a la orden. " . $respuestaBot;
    }
    return $respuestaBot;
}
// ... (El resto del código PHP y HTML sigue igual)


// Procesar el mensaje del usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensaje = htmlspecialchars($_POST["mensaje"]);
    
    // Aquí la función obtenerProductos($conn) ahora existe y se llama correctamente.
    $productos = obtenerProductos($conn); 
    
    if (!isset($_SESSION['historial'])) {
        $_SESSION['historial'] = [];
    }
    $historial = $_SESSION['historial'];
    
    $primeraInteraccion = count($historial) === 0;
    
    $respuesta = enviarMensajeAGemini($mensaje, $productos, $historial, $primeraInteraccion);
    
    $historial[] = [
        "usuario" => $mensaje,
        "bot" => $respuesta
    ];
    
    if (count($historial) > 10) {
        $historial = array_slice($historial, -10);
    }
    
    $_SESSION['historial'] = $historial;
    
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
    <script src="../Sistema/js/tailwind_config.js"></script>
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
        <p>&copy; 2025 Autorepuestos, C.A. - Todos los derechos reservados</p>
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