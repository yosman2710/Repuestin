<?php

require '../logica/validar.php';
require '../logica/conexionbdd.php';

session_start();

if (!isset($_SESSION['id'])) {
    header('location:../login-sesion/loginCliente.php?error_message=Por favor inicie sesión');
    exit();
} else {
    if ((time() - $_SESSION['time']) > 600) {
        session_unset();
        session_destroy();
        header('location:../login-sesion/loginCliente.php?error_message=La sesión ha expirado');
        exit();
    }
}

$_SESSION['time'] = time();

$client_id = $_SESSION['id'];
$query = "SELECT nombre_empresa, nombre_encargado, rif FROM clientes WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
$client_data = $result->fetch_assoc();

// Obtener productos del carrito desde la base de datos
$cart_items = [];
$subtotal = 0;
$total_items = 0;

// Consulta para obtener los productos en el carrito
$query = "SELECT c.cantidad, p.* FROM carrito c
        INNER JOIN productos p ON c.producto_id = p.id_producto
        WHERE c.cliente_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

while ($item = $result->fetch_assoc()) {
    $item['subtotal'] = $item['cantidad'] * $item['precio_producto'];
    $item['foto'] = obtenerRutasArchivos($item['id_producto']);
    $cart_items[] = $item;
    $subtotal += $item['subtotal'];
    $total_items += $item['cantidad'];
}

$iva = $subtotal * 0.16;
$total = $subtotal + $iva;
?>
<!DOCTYPE html>
<html lang="es" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Autorepuestos</title>
    <link rel="shortcut icon" href="./LOGO-VENTANA.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/tailwind_config.js"></script>
    <script src="../js/mantener_modo_claro_oscuro.js"></script>
    <link rel="stylesheet" href="../css/global_style.css">

    <style>
        /* Ocultar flechas en campos de entrada de tipo number */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body class="bg-pattern transition-colors duration-200">
    <!-- Navbar -->
    <nav class="bg-custom-blue dark:bg-gray-800 text-white px-6 py-4 fixed w-full top-0 z-50 shadow-lg">
        <div class="flex justify-between items-center">
            <div class="text-xl font-bold">
                <a href="./cliente.php"
                    class="text-xl hover:text-gray-200 transition-colors duration-200 flex items-center gap-2 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span class="text-sm">Volver</span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm bg-blue-900 px-3 py-1 rounded-full">
                    Bienvenido, <?php echo htmlspecialchars($client_data['nombre_encargado']); ?>
                </span>
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

    <!-- Contenido Principal -->
    <main class="pt-24 px-6 pb-20">
        <h2 class="text-2xl font-bold mb-6 dark:text-white">Carrito de Compras</h2>

        <?php if (empty($cart_items)): ?>
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 text-center">
                <p class="text-gray-600 dark:text-gray-400 mb-4">Tu carrito está vacío</p>
                <a href="catalogo.php"
                    class="inline-block bg-custom-blue hover:bg-custom-blue-light dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-6 py-2 rounded-md transition-colors duration-200">
                    Ir al Catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="flex gap-6">
                <!-- Productos en el carrito -->
                <div class="flex-grow">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                            <div class="flex items-center border-b dark:border-gray-700 pb-4">
                                <img src="<?php echo $item['foto']; ?>" alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>"
                                    class="w-24 h-24 object-cover rounded-lg">
                                <div class="ml-4 flex-grow">
                                    <h3 class="text-lg font-semibold dark:text-white">
                                        <?php echo htmlspecialchars($item['nombre_producto']); ?>
                                    </h3>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        Código: <?php echo htmlspecialchars($item['numero_de_parte']); ?>
                                    </p>
                                    <div class="flex items-center mt-2">
                                        <div class="flex items-center border rounded-lg dark:border-gray-600">
                                            <button class="px-3 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                onclick="updateCartQuantity(<?php echo $item['id_producto']; ?>, -1, <?php echo $item['stock_producto']; ?>)">-</button>
                                            <input type="number" value="<?php echo $item['cantidad']; ?>"
                                                class="w-12 text-center border-x dark:border-gray-600 bg-transparent dark:text-white no-arrows"
                                                id="quantity_<?php echo $item['id_producto']; ?>"
                                                max="<?php echo $item['stock_producto']; ?>"
                                                onchange="updateCartQuantityDirect(<?php echo $item['id_producto']; ?>, this.value, <?php echo $item['stock_producto']; ?>)">
                                            <button class="px-3 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                onclick="updateCartQuantity(<?php echo $item['id_producto']; ?>, 1, <?php echo $item['stock_producto']; ?>)">+</button>
                                        </div>
                                        <button onclick="removeFromCart(<?php echo $item['id_producto']; ?>)"
                                            class="ml-4 text-red-600 hover:text-red-800 dark:hover:text-red-400">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold dark:text-white">
                                        $<?php echo number_format($item['subtotal'], 2); ?>
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        $<?php echo number_format($item['precio_producto'], 2); ?> c/u
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resumen del pedido -->
                <div class="w-80">
                    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 sticky top-24">
                        <h3 class="text-lg font-semibold mb-4 dark:text-white">Resumen del pedido</h3>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">
                                    Subtotal (<?php echo $total_items; ?> items)
                                </span>
                                <span class="font-semibold dark:text-white">
                                    $<?php echo number_format($subtotal, 2); ?>
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">IVA (16%)</span>
                                <span class="font-semibold dark:text-white">
                                    $<?php echo number_format($iva, 2); ?>
                                </span>
                            </div>
                        </div>
                        <div class="border-t dark:border-gray-700 pt-4 mb-4">
                            <div class="flex justify-between">
                                <span class="font-semibold dark:text-white">Total</span>
                                <span class="font-semibold text-lg dark:text-white">
                                    $<?php echo number_format($total, 2); ?>
                                </span>
                            </div>
                        </div>
                        <button onclick="createOrder()"
                            class="w-full bg-custom-blue hover:bg-custom-blue-light text-white py-2 px-4 rounded-lg transition-colors">
                            Proceder al pago
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-custom-blue dark:bg-gray-800 text-white text-center py-4 fixed bottom-0 w-full text-sm">
        <p>&copy; 2025 Autorepuestos, C.A. - Todos los derechos reservados</p>
    </footer>

    <script>

        /**
         * Updates the quantity of a product in the shopping cart
         * @param {number} productId - The ID of the product to update
         * @param {number} change - The amount to change the quantity by (+1 or -1)
         * @param {number} maxStock - The maximum stock available for this product
         */
        function updateCartQuantity(productId, change, maxStock) {
            const quantityInput = document.getElementById(`quantity_${productId}`);
            let newQuantity = parseInt(quantityInput.value) + change;

            if (newQuantity < 1) return;
            if (newQuantity > maxStock) {
                alert('No hay suficiente stock disponible');
                return;
            }

            updateCart(productId, newQuantity);
        }

        /**
         * Updates the quantity of a product in the shopping cart directly from input
         * @param {number} productId - The ID of the product to update
         * @param {number} newQuantity - The new quantity entered by the user
         * @param {number} maxStock - The maximum stock available for this product
         */
        function updateCartQuantityDirect(productId, newQuantity, maxStock) {
            newQuantity = parseInt(newQuantity);

            if (newQuantity < 1) {
                alert('La cantidad debe ser al menos 1');
                return;
            }
            if (newQuantity > maxStock) {
                alert('No hay suficiente stock disponible');
                return;
            }

            updateCart(productId, newQuantity);
        }

        /**
         * Updates the cart with the new quantity
         * @param {number} productId - The ID of the product to update
         * @param {number} newQuantity - The new quantity to set
         */
        function updateCart(productId, newQuantity) {
            fetch('../logica/cart-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update quantity
                    const quantityInput = document.getElementById(`quantity_${productId}`);
                    quantityInput.value = newQuantity;

                    // Find product container and price elements
                    const productContainer = quantityInput.closest('.bg-white.dark\\:bg-gray-800');
                    const pricePerUnit = parseFloat(productContainer.querySelector('.text-right .text-sm.text-gray-500').textContent.replace(/[^\d.]/g, ''));
                    const productSubtotal = (newQuantity * pricePerUnit).toFixed(2);

                    // Update product subtotal
                    productContainer.querySelector('.text-right .text-lg.font-semibold').textContent = `$${productSubtotal}`;

                    // Calculate cart totals
                    let cartSubtotal = 0;
                    let cartItems = 0;

                    // Sum up all products
                    document.querySelectorAll('.flex-grow .bg-white.dark\\:bg-gray-800').forEach(container => {
                        const qty = parseInt(container.querySelector('input[type="number"]').value);
                        const price = parseFloat(container.querySelector('.text-right .text-sm.text-gray-500').textContent.replace(/[^\d.]/g, ''));
                        cartItems += qty;
                        cartSubtotal += qty * price;
                    });

                    const iva = cartSubtotal * 0.16;
                    const total = cartSubtotal + iva;

                    // Update summary section
                    const summarySection = document.querySelector('.w-80 .bg-white.dark\\:bg-gray-800');
                    summarySection.querySelector('.space-y-2 .font-semibold.dark\\:text-white').textContent = `$${cartSubtotal.toFixed(2)}`;
                    summarySection.querySelector('.space-y-2').children[1].querySelector('.font-semibold.dark\\:text-white').textContent = `$${iva.toFixed(2)}`;
                    summarySection.querySelector('.border-t .font-semibold.text-lg.dark\\:text-white').textContent = `$${total.toFixed(2)}`;
                    summarySection.querySelector('.text-gray-600.dark\\:text-gray-400').textContent = `Subtotal (${cartItems} items)`;
                } else {
                    alert('Error al actualizar el carrito');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar el carrito');
            });
        }

        // Eliminar producto del carrito
        function removeFromCart(productId) {
            if (confirm('¿Está seguro que desea eliminar este producto del carrito?')) {
                fetch('../logica/cart-handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=remove&product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al eliminar del carrito');
                    });
            }
        }

        // Función para recalcular totales
        function updateTotals() {
            const subtotalElements = document.querySelectorAll('.bg-white .text-lg.font-semibold');
            let subtotal = 0;
            let totalItems = 0;

            // Calcular nuevo subtotal y total de items
            subtotalElements.forEach(element => {
                if (element.textContent.includes('$')) {
                    const amount = parseFloat(element.textContent.replace('$', ''));
                    subtotal += amount;
                    const quantity = parseInt(element.closest('.bg-white').querySelector('input[type="number"]').value);
                    totalItems += quantity;
                }
            });

            // Calcular IVA y total
            const iva = subtotal * 0.16;
            const total = subtotal + iva;

            // Actualizar valores en el resumen
            const subtotalDisplay = document.querySelector('.space-y-2 .font-semibold.dark\\:text-white');
            const ivaDisplay = document.querySelector('.space-y-2').children[1].querySelector('.font-semibold.dark\\:text-white');
            const totalDisplay = document.querySelector('.border-t .font-semibold.text-lg.dark\\:text-white');
            const itemCountDisplay = document.querySelector('.text-gray-600.dark\\:text-gray-400');

            if (subtotalDisplay) subtotalDisplay.textContent = `$${subtotal.toFixed(2)}`;
            if (ivaDisplay) ivaDisplay.textContent = `$${iva.toFixed(2)}`;
            if (totalDisplay) totalDisplay.textContent = `$${total.toFixed(2)}`;
            if (itemCountDisplay) itemCountDisplay.textContent = `Subtotal (${totalItems} items)`;
        }
    </script>
</body>

</html>

<script>
function createOrder() {
    if (confirm('¿Está seguro que desea realizar la orden?')) {
        fetch('../logica/create-order.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Orden creada exitosamente. El administrador revisará su orden.');
                window.location.href = 'cliente.php';
            } else {
                alert(data.message || 'Error al crear la orden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la orden');
        });
    }
}
</script>