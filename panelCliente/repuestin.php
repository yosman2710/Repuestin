    <?php
    // Iniciar la sesión para obtener el ID del cliente
    session_start();

    // ----------------------------------------------------------------------------------
    // --- ⚠️ CONFIGURACIÓN DE LA BASE DE DATOS (MySQLi) ---
    // ⚠️ ATENCIÓN: Reemplaza estos valores por los correctos de tu entorno
    $servername = "localhost";
    $username = "root"; // RECUERDA CAMBIAR 'root'
    $password = "";     // RECUERDA USAR UNA CONTRASEÑA FUERTE
    $dbname = "repuestos_tiramealgo";

    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        // Detener la ejecución si falla la conexión a la DB
        die("¡Verga, mi pana! Falló la conexión con la base de datos: " . $conn->connect_error);
    }
    // ----------------------------------------------------

    $cliente_id_actual = $_SESSION['id'] ?? 1; // ✅ Usamos esta variable

// API Key (Debe ser protegida en producción. Idealmente cargada de un archivo de entorno)
$apiKey = "AIzaSyBMvGi9_c-_yYO6zQYjD5odXBAfQHjvuLg";
// ----------------------------------------------------

// 🚀 CONSULTA DEL CLIENTE CORREGIDA 🚀
$query = "SELECT nombre_empresa, nombre_encargado, rif FROM clientes WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $cliente_id_actual); // 🎯 CORRECCIÓN: Usar $cliente_id_actual
$stmt->execute();
$result = $stmt->get_result();
$client_data = $result->fetch_assoc();
$stmt->close(); // 🚨 Cerrar el statement aquí es importa
    // --- FUNCIÓN 1: OBTENER PRODUCTOS ---
    /**
     * Obtiene todos los productos disponibles en stock.
     */
    function obtenerProductos($conn)
    {
        // Seleccionamos solo los campos necesarios y filtramos por stock > 0
        $sql = "SELECT nombre_producto, precio_producto, stock_producto FROM productos WHERE stock_producto > 0";
        $result = $conn->query($sql);
        $productos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }
        return $productos;
    }


    // --- FUNCIÓN 2: GUARDAR INTERACCIÓN EN DB (Tabla: historial_chat) ---
    /**
     * Guarda la interacción actual (mensaje del usuario y respuesta del bot) en la DB.
     */
    function guardarInteraccion($conn, $clienteId, $rol, $mensaje)
    {
        // rol es 'usuario' o 'bot'
        $sql = "INSERT INTO historial_chat (cliente_id, rol, mensaje, fecha_creacion) VALUES (?, ?, ?, NOW())";
            
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            error_log("Error al preparar la consulta de guardado: " . $conn->error);
            return false;
        }

        // Parámetros: i=integer, s=string, s=string
        $stmt->bind_param("iss", $clienteId, $rol, $mensaje);
        $exito = $stmt->execute();
        $stmt->close();
        
        return $exito;
    }


    // --- FUNCIÓN 3: OBTENER HISTORIAL (Para el Frontend) ---
    /**
     * Obtiene el historial de mensajes de un cliente.
     */
    function obtenerHistorialDB($conn, $clienteId, $limite = 20)
    {
        // Seleccionar los 20 mensajes más recientes del cliente, ordenados cronológicamente
        $sql = "SELECT rol, mensaje FROM historial_chat WHERE cliente_id = ? ORDER BY fecha_creacion ASC LIMIT ?";
        
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            error_log("Error al preparar la consulta de historial: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("ii", $clienteId, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $historial = [];
        while ($row = $result->fetch_assoc()) {
            $historial[] = $row;
        }
        
        $stmt->close();
        return $historial; 
    }


    // --- FUNCIÓN 4: ENVIAR MENSAJE A GEMINI (PROMPT INJECTION) ---
    /**
     * Envía el mensaje actual a la API de Gemini. No usa el historial para mantener la arquitectura stateless.
     */
    function enviarMensajeAGemini($mensaje, $productos, $apiKey, $clientData) // ✅ Recibe $clientData
{
    $nombreEmpresa = $clientData['nombre_empresa'] ?? 'Empresa Desconocida'; // Línea ~135
    $nombreEncargado = $clientData['nombre_encargado'] ?? 'Mi llave'; 
    $rif = $clientData['rif'] ?? 'No Disponible';
        $model = 'gemini-2.5-flash';

        // 1. Formatear los productos para el prompt
        $productosFormateados = "Lista de Productos:\n";
        foreach ($productos as $producto) {
            $productosFormateados .= "- {$producto['nombre_producto']}: \${$producto['precio_producto']} (Stock: {$producto['stock_producto']})\n";
        }

        // 2. Crear la Instrucción del Sistema/Prompt Maestro
        $promptMaestro = "
            Eres un chatbot llamado **Repuestin**, especializado en repuestos automotrices. 
            Tu personalidad es la de un vendedor maracucho: **confiado, ameno y respetuoso**. 
            Siempre preséntate como Repuestín y saluda con jerga maracucha.
            
            Eres **stateless**, lo que significa que debes responder a cada mensaje de forma independiente.
            tambien quiero que uses los datos de la cuenta del cliente.
            Aquí tienes la información de la cuenta del cliente:
            Nombre de su Empresa: {$nombreEmpresa}
            Nombre del cliente: {$nombreEncargado}
            RIF: {$rif}
            saluda al cliente usando su nombre de encargado.
            
            Aquí tienes la lista de productos disponibles:
            $productosFormateados
            
            --- REGLAS DE NEGOCIO ---
            1. **Presupuestos (Formato Estricto):**
            Presupuesto # [Número incremental]
            [cantidad de producto], [producto en si]
            TOTAL: [TOTAL A PAGAR en $]
            
            2. **Delivery Maracaibo (Base):** Calcula un valor aproximado de **$2 por kilómetro**. Menciona *zonas de referencia* para el cliente.
            
            3. **Delivery San Francisco:** El costo fijo es de **$13**.
            
            4. **Delivery GRATIS:** Si la compra supera los **$200**.

            5. **Interacción:** Mantén las respuestas **breves** y usa jerga maracucha. **Prioriza la venta**.

            6. **No disponible:** Sé honesto y sugiérele buscar más información o contactar un humano.
            
            --- PREGUNTA ACTUAL DEL USUARIO ---
            $mensaje
        ";

    // 3. Configuración de la API
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $data = json_encode([
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    [
                        "text" => $promptMaestro // Se envía el prompt maestro + el mensaje
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.8,
        ]
    ]);

    // 4. Realizar la petición HTTP
    $options = [
        "http" => [
            "header" => "Content-Type: application/json",
            "method" => "POST",
            "content" => $data,
            "timeout" => 30,
        ]
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return "Lo siento, mi pana. Hubo un error de conexión con la IA.";
    }

    $response = json_decode($result, true);

    if (isset($response['error'])) {
        $error_message = $response['error']['message'] ?? "Error desconocido de la API.";
        error_log("Error de la API de Gemini: " . $error_message);
        return "¡Verga, mi llave! La IA tuvo un percance. Error: " . (isset($response['error']['message']) ? substr($response['error']['message'], 0, 100) . '...' : 'Desconocido');
    }

    $respuestaBot = $response['candidates'][0]['content']['parts'][0]['text'] ?? "No se pudo obtener una respuesta válida. Intenta de nuevo, pues.";

    return $respuestaBot;
}

// ----------------------------------------------------------------------------------
// --- PROCESAMIENTO PRINCIPAL DE LA SOLICITUD AJAX ---
// ----------------------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mensaje"])) {
    $mensaje = htmlspecialchars($_POST["mensaje"]);

    // 1. Guardar el mensaje del usuario en la DB
    guardarInteraccion($conn, $cliente_id_actual, 'usuario', $mensaje);
    
    // 2. Obtener productos y la respuesta de Gemini (stateless, sin pasar historial)
   $productos = obtenerProductos($conn);
    // ✅ CORRECCIÓN: Ahora pasamos $client_data
    $respuesta = enviarMensajeAGemini($mensaje, $productos, $apiKey, $client_data);

    // 3. Guardar la respuesta del bot en la DB
    guardarInteraccion($conn, $cliente_id_actual, 'bot', $respuesta);
    
    // 4. Enviar la respuesta al cliente (Frontend)
    echo $respuesta;
    exit;
}


// ----------------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repuestin</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/global_style.css"> 
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    
    <style>
        /* Estilos CSS */
        #particles-js { position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: -1; }
        .typing-indicator-container { display: flex; align-items: center; background-color: #f3f4f6; border-radius: 9999px; padding: 5px; margin-bottom: 0.5rem; }
        .dark .typing-indicator-container { background-color: #4b5563; }
        .typing-indicator { display: inline-block; width: 0.5em; height: 0.5em; border-radius: 50%; background-color: #969696; margin: 0 2px; animation: blink 0.7s infinite alternate; }

        @keyframes blink { 0% { opacity: 1; } 100% { opacity: 0.5; } }

        .emoji-container { position: absolute; top: -100px; right: 20px; transition: transform 3s ease-in-out; font-size: 4rem; }
        .emoji-container.left { transform: translateX(-450%); }
        .custom-shadow { box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); }
        #chatbox { border: none; overflow-y: auto; }
    </style>

    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
</head>

<body class="bg-pattern transition-colors duration-200">
    <div id="particles-js" class="fixed inset-0 z-0"></div>

        <nav class="bg-custom-wineDeep dark:bg-custom-wineDeep text-custom-silver px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
            <a href="cliente.php" class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span class="text-sm">Volver</span>
            </a>
            <div class="text-xl font-bold">Chatbot - Repuestin</div>
            <div class="flex items-center gap-4">
                <button onclick="toggleDarkMode()" class="p-2 rounded-full bg-custom-wineDark dark:bg-custom-red hover:bg-custom-red dark:hover:bg-custom-wineDark transition-colors duration-200" aria-label="Alternar entre modo oscuro y claro">
                    <span class="dark:hidden">🌙</span>
                    <span class="hidden dark:inline">☀️</span>
                </button>
                <a href="../logica/cerrar-sesion.php" class="hover:underline">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <main class="min-h-screen flex flex-col items-center justify-center px-4 pt-16">
        <div class="bg-custom-silverLight dark:bg-custom-steelDark p-10 rounded-lg custom-shadow max-w-2xl w-full relative">
            
                <div id="chatbox" class="h-80 mb-4 p-4 flex flex-col space-y-2">
                <?php
                $historialCargado = obtenerHistorialDB($GLOBALS['conn'], $GLOBALS['cliente_id_actual']);

                if (is_array($historialCargado)) {
                    foreach ($historialCargado as $interaccion) {
                            // Definir clases CSS basadas en el rol
                        if ($interaccion['rol'] === 'usuario') {
                            $clase = 'bg-blue-500 text-white self-end text-right';
                        } else { // rol === 'bot'
                            // Ajuste para que se vea bien en ambos temas
                            $clase = 'bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white self-start text-left';
                        }

                        // Mostrar el mensaje
                        echo "<div class='rounded-lg p-2 mb-2 max-w-xs {$clase}'>{$interaccion['mensaje']}</div>"; 
                        }
                }
                ?>
                </div>
            
                <input type="text" id="userInput" placeholder="Escribe tu mensaje..." class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 focus:outline-none focus:border-custom-blue dark:focus:border-blue-500 dark:bg-custom-gray dark:text-white">
            <button id="sendButton" class="w-full bg-custom-orange hover:bg-custom-wineDark dark:bg-custom-orange dark:hover:bg-custom-red text-custom-silver py-2 px-4 rounded-md transition-colors duration-200 font-semibold mt-2">
                Enviar
            </button>
        </div>
    </main>

        <footer class="bg-custom-steelDark dark:bg-custom-black text-white backdrop-blur-sm text-center py-4 fixed bottom-0 w-full text-sm sm:text-base shadow-lg">
        <p>&copy; 2025 Autorepuestos TirameAlgo, C.A. - Todos los derechos reservados</p>
    </footer>

    <script>
        // --- Lógica de Partículas y Dark Mode (sin cambios) ---
        let particlesInstance = null;

        function initParticles(theme) {
            // Lógica de inicialización y actualización de partículas
            // (Asumo que esta lógica funciona y no la modifico)
            if (particlesInstance) {
                const color = theme === "dark" ? "#e5e7eb" : "#1b1e34";
                particlesInstance.particles.color.value = color;
                particlesInstance.particles.line_linked.color = color;
                particlesInstance.fn.particlesDraw();
            } else {
                particlesInstance = particlesJS("particles-js", {
                    particles: { /* ... configuración de partículas ... */
                        number: { value: 80, density: { enable: true, value_area: 800 }},
                        color: { value: theme === "dark" ? "#e5e7eb" : "#1b1e34" },
                        shape: { type: "circle", stroke: { width: 0, color: "#000000" }},
                        opacity: { value: 0.5, random: false, anim: { enable: false, speed: 1, opacity_min: 0.1, sync: false }},
                        size: { value: 3, random: true, anim: { enable: false, speed: 40, size_min: 0.1, sync: false }},
                        line_linked: { enable: true, distance: 150, color: theme === "dark" ? "#e5e7eb" : "#13141bff", opacity: 0.4, width: 1 },
                        move: { enable: true, speed: 3, direction: "none", random: false, straight: false, out_mode: "bounce", bounce: false, attract: { enable: false, rotateX: 600, rotateY: 1200 }}
                    },
                    interactivity: { /* ... configuración de interactividad ... */
                        detect_on: "canvas", events: { onhover: { enable: true, mode: "bubble" }, onclick: { enable: true, mode: "push" }, resize: true },
                        modes: { bubble: { distance: 200, size: 6, duration: 2, opacity: 0.8, speed: 3 }, push: { particles_nb: 4 }}
                    },
                    retina_detect: true
                });
            }
        }

        const savedTheme = localStorage.getItem('theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.classList.toggle('dark', savedTheme === 'dark');
        initParticles(savedTheme);

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
            initParticles(isDarkMode ? 'light' : 'dark');
        }
        // ----------------------------------------------------

        const chatbox = document.getElementById('chatbox');
        const userInput = document.getElementById('userInput');
        const sendButton = document.getElementById('sendButton');

        // Scroll al final al cargar la página (para ver el historial)
        document.addEventListener('DOMContentLoaded', () => {
            chatbox.scrollTop = chatbox.scrollHeight;
        });

        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendButton.click();
            }
        });

        sendButton.addEventListener('click', () => {
            const userMessage = userInput.value;
            if (userMessage.trim() === '') return;

            // 1. Mostrar mensaje del usuario inmediatamente
            const userMessageElement = document.createElement('div');
            userMessageElement.textContent = userMessage;
            userMessageElement.classList.add('bg-custom-orange', 'text-white', 'rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-end', 'text-right');
            chatbox.appendChild(userMessageElement);

            // 3. Mostrar indicador de escritura del bot
            const typingIndicator = document.createElement('div');
            typingIndicator.classList.add('typing-indicator-container', 'self-start');
            typingIndicator.innerHTML = `
                <div class="typing-indicator"></div>
                <div class="typing-indicator"></div>
                <div class="typing-indicator"></div>
            `;
            chatbox.appendChild(typingIndicator);
            chatbox.scrollTop = chatbox.scrollHeight;

            // 4. Enviar mensaje al backend
            fetch("<?php echo $_SERVER['PHP_SELF']; ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: `mensaje=${encodeURIComponent(userMessage)}`,
                })
                .then(response => response.text())
                .then(response => {
                    // 5. Eliminar el indicador de escritura
                    if (chatbox.contains(typingIndicator)) {
                        chatbox.removeChild(typingIndicator);
                    }

                    // 6. Mostrar respuesta del chatbot
                    const botMessageElement = document.createElement('div');
                    botMessageElement.textContent = response;
                    // Clases dinámicas para dark mode
                    const botClasses = ['rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-start', 'text-left', 'bg-custom-gray', 'dark:bg-custom-gray', 'text-gray-800', 'dark:text-white'];
                    botMessageElement.classList.add(...botClasses);
                    chatbox.appendChild(botMessageElement);

                    // 7. Desplazar hacia abajo
                    chatbox.scrollTop = chatbox.scrollHeight;
                })
                .catch(error => {
                    console.error("Error al enviar mensaje:", error);

                    // Si hay error, también quitar el indicador
                    if (chatbox.contains(typingIndicator)) {
                        chatbox.removeChild(typingIndicator);
                    }

                    const errorMessageElement = document.createElement('div');
                    errorMessageElement.textContent = "Error: ¡Verga! No se pudo conectar con el servidor. Intenta de nuevo.";
                    errorMessageElement.classList.add('bg-red-500', 'text-white', 'rounded-lg', 'p-2', 'mb-2', 'max-w-xs', 'self-start', 'text-left');
                    chatbox.appendChild(errorMessageElement);
                    chatbox.scrollTop = chatbox.scrollHeight;
                });

            // Limpiar el input
            userInput.value = '';
        });
    </script>
    <?php
// ✅ MOVER EL CIERRE DE CONEXIÓN AQUÍ, DESPUÉS DE LA RENDERIZACIÓN
if (isset($conn)) {
    $conn->close();
}
?>
</body>

</html>
