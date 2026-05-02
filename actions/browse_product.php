<?php
// Start session and check for user
session_start();
// If you want to use the session to greet the user or verify access
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>GCST Library - Browse Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <script>
    // Security check - redirect if no session
    window.addEventListener('DOMContentLoaded', function() {
        fetch('http://localhost/GCST_Track_System/actions/get_user.php')
            .then(res => res.json())
            .then(data => {
                if (!data.student_id) {
                    window.location.href = "http://localhost/GCST_Track_System/pages/sign_up.html";
                }
            })
            .catch(() => {
                window.location.href = "http://localhost/GCST_Track_System/pages/sign_up.html";
            });
    });
    </script>

    <style>
        /* Reusing your existing styles */
        .menu-item i { margin-right: 10px; width: 20px; text-align: center; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #f9f9f9; color: #333; }
        header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 2rem; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); background: white; }
        .logo { display: flex; align-items: center; gap: 0.5rem; }
        .logo img { width: 30px; height: 30px; }
        nav a { margin-left: 1.5rem; text-decoration: none; color: #333; font-weight: 500; cursor: pointer; }
        .hero { background: linear-gradient(to right, #e0e7ff, #dde0eb); padding: 2rem; text-align: center; border-radius: 10px; margin: 2rem; margin-bottom: 1rem; box-shadow: 0 8px 10px rgba(0,0,0,0.1); }
        .hero h1 { font-size: 2rem; margin: 0; }
        .category-tabs { display: flex; justify-content: center; gap: 1rem; margin: 2rem; flex-wrap: wrap; }
        .category-tab { padding: 0.75rem 1.5rem; border: 2px solid #dbeafe; background: white; border-radius: 25px; cursor: pointer; font-weight: 500; transition: all 0.3s ease; }
        .category-tab:hover { border-color: #3890bc; color: #3890bc; }
        .category-tab.active { background: linear-gradient(to right, #3890bc, #32a9e5); color: white; border-color: #3890bc; }
        .products-container { padding: 0 2rem; margin-bottom: 2rem; }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; }
        .product-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 16px rgba(0,0,0,0.15); }
        .product-image { width: 100%; height: 220px; background: #e0e7ff; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-info { padding: 1.25rem; }
        .product-name { font-weight: 600; font-size: 1rem; margin-bottom: 0.5rem; min-height: 2.2em; }
        .product-price { font-size: 1.25rem; color: #3890bc; font-weight: bold; margin-bottom: 1rem; }
        .product-actions { display: flex; gap: 0.5rem; }
        .btn-rent, .btn-buy { flex: 1; padding: 0.6rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 0.9rem; }
        .btn-rent { background-color: #f7efb7; color: #92400e; }
        .btn-buy { background-color: #3890bc; color: white; }
        .empty-state { text-align: center; padding: 3rem; color: #666; }
        .dropdown-menu { display: none; position: absolute; top: 65px; right: 20px; background-color: #f0f4ff; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15); width: 220px; z-index: 999; }
        .menu-item { display: flex; align-items: center; padding: 12px 16px; text-decoration: none; color: #333; }
        .menu-item:hover { background-color: #dbeafe; }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="http://localhost/GCST_Track_System/assets/images/icons/granbylogo.png" alt="Logo">
            <strong>GCST Tracking System</strong>
        </div>
        <nav class="nav-links">
            <a href="InUser_home.html">Home</a>
            <a href="browse_products.html">Products</a>
            <a href="#" id="menu-icon">☰</a>
        </nav>
    </header>

    <div class="dropdown-menu" id="dropdown-menu">
        <a class="menu-item" href="user_profile.html"><i class="fas fa-user"></i> Profile</a>
        <a class="menu-item" href="http://localhost/GCST_Track_System/actions/log_out.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </div>

    <section class="hero">
        <h1>Browse Products</h1>
    </section>

    <div class="category-tabs">
        <button class="category-tab active" data-category="all">All Products</button>
        <button class="category-tab" data-category="Books">📚 Books</button>
        <button class="category-tab" data-category="Uniforms">👕 Uniforms</button>
        <button class="category-tab" data-category="Accessories">🎒 Accessories</button>
    </div>

    <div class="products-container">
        <div class="products-grid" id="products-grid">
            </div>
    </div>

    <script>
        // Menu Toggle
        const menuIcon = document.getElementById('menu-icon');
        const dropdownMenu = document.getElementById('dropdown-menu');
        menuIcon.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        // Fetch Products from Database
        async function fetchProducts(category = 'all') {
            const grid = document.getElementById('products-grid');
            grid.innerHTML = '<div class="empty-state"><p>Loading products...</p></div>';

            try {
                const response = await fetch('http://localhost/GCST_Track_System/actions/fetch.php');
                const products = await response.json();

                grid.innerHTML = '';
                const filtered = category === 'all' ? products : products.filter(p => p.product_category === category);

                if (filtered.length === 0) {
                    grid.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><p>No products found in this category.</p></div>';
                    return;
                }

                filtered.forEach(p => {
                    const card = document.createElement('div');
                    card.className = 'product-card';
                    // Use product_image from DB or a default emoji if null
                    const imageContent = p.product_image ? `<img src="${p.product_image}">` : '📦';
                    
                    card.innerHTML = `
                        <div class="product-image">${imageContent}</div>
                        <div class="product-info">
                            <div class="product-name">${p.product_name}</div>
                            <div class="product-price">₱${p.price}</div>
                            <div class="product-actions">
                                <button class="btn-rent" onclick="handleAction('rent', ${p.product_id})">Rent</button>
                                <button class="btn-buy" onclick="handleAction('buy', ${p.product_id})">Buy</button>
                            </div>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            } catch (error) {
                grid.innerHTML = '<div class="empty-state"><p>Error loading products.</p></div>';
            }
        }

        function handleAction(type, id) {
            // This connects to the request_product.php we made earlier
            fetch('http://localhost/GCST_Track_System/actions/request_product.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: id, student_id: '<?php echo $student_id; ?>' })
            })
            .then(res => res.json())
            .then(data => alert(data.message));
        }

        // Category Tab Filtering
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                fetchProducts(tab.getAttribute('data-category'));
            });
        });

        // Initial Load
        fetchProducts('all');
    </script>
</body>
</html>
