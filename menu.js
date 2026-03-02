window.addEventListener("DOMContentLoaded", () => {
    // Show alert welcome
    // alert("👋 Welcome to TastyBites! Enjoy exploring our delicious menu.");

    // Create floating welcome box
    const welcomeBox = document.createElement("div");
    welcomeBox.style.position = "fixed";
    welcomeBox.style.top = "20px";
    welcomeBox.style.left = "50%";
    welcomeBox.style.transform = "translateX(-50%)";
    welcomeBox.style.padding = "15px 20px";
    welcomeBox.style.backgroundColor = "#fff0f5";
    welcomeBox.style.border = "2px solid #ff99cc";
    welcomeBox.style.borderRadius = "10px";
    welcomeBox.style.fontSize = "18px";
    welcomeBox.style.color = "#800040";
    welcomeBox.style.boxShadow = "0 0 12px rgba(0,0,0,0.2)";
    welcomeBox.style.zIndex = "999";
    welcomeBox.style.opacity = "0";
    welcomeBox.style.transition = "opacity 1s";
    welcomeBox.textContent = `👋 Welcome to TastyBites 🍽️ Enjoy your meal with us!`;

    document.body.appendChild(welcomeBox);

    setTimeout(() => {
        welcomeBox.style.opacity = "1";
    }, 200);

    // Auto-remove after 6 seconds
    setTimeout(() => {
        welcomeBox.style.opacity = "0";
        setTimeout(() => welcomeBox.remove(), 1000);
    }, 4000);
});

// --- CART SYSTEM ---
let cart = [];
let cartVisible = false;

// --- Create cart box ---
const cartBox = document.createElement("div");
cartBox.id = "cart-box";
cartBox.style.display = "none";
cartBox.style.position = "absolute";
cartBox.style.top = "120px";
cartBox.style.right = "20px";
cartBox.style.width = "270px";
cartBox.style.maxHeight = "300px";        // ✅ Set max height for scroll
cartBox.style.overflowY = "auto";         // ✅ Enable vertical scrolling
cartBox.style.backgroundColor = "#fff";
cartBox.style.border = "1px solid #aaa";
cartBox.style.borderRadius = "8px";
cartBox.style.boxShadow = "0 0 10px rgba(0,0,0,0.2)";
cartBox.style.padding = "10px";
cartBox.style.transition = "max-height 0.5s ease, opacity 0.5s ease";
cartBox.style.opacity = "0";
cartBox.style.zIndex = "100";
document.body.appendChild(cartBox);

// --- Toggle cart ---
const cartBtn = document.getElementById("view-cart-btn");
cartBtn.addEventListener("click", () => {
    cartVisible = !cartVisible;
    if (cartVisible) {
        cartBox.style.display = "block";
        setTimeout(() => {
            cartBox.style.opacity = "1";
        }, 20);
        showCartItems();
    } else {
        cartBox.style.opacity = "0";
        setTimeout(() => {
            if (!cartVisible) cartBox.style.display = "none";
        }, 500);
    }
});

// --- Show cart items ---
function showCartItems() {
    cartBox.innerHTML = "<h4 style='text-align:center;'>🧺 Your Cart</h4><hr>";
    if (cart.length === 0) {
        cartBox.innerHTML += "<p style='text-align:center;'>Cart is empty!</p>";
        return;
    }

    let total = 0;
    cart.forEach((item, index) => {
        total += item.price * item.qty;
        cartBox.innerHTML += `
            <div style="margin-bottom: 12px;">
                <strong>${item.name}</strong><br>
                Rs. ${item.price} x ${item.qty}
                <div style="margin-top: 5px;">
                    <button onclick="decreaseQty(${index})" style="margin-right:5px;">-</button>
                    <button onclick="increaseQty(${index})">+</button>
                </div>
            </div>
        `;
    });

    cartBox.innerHTML += `<hr><p><strong>Total: Rs. ${total}</strong></p>`;
}

// --- Add to cart ---
document.querySelectorAll(".button").forEach((btn) => {
    btn.addEventListener("click", function (e) {
        e.preventDefault();

        let form = btn.closest("form");
        let item = form.querySelector('input[name="item"]').value;
        let price = parseFloat(form.querySelector('input[name="price"]').value);

        let existing = cart.find(i => i.name === item);
        if (existing) {
            existing.qty += 1;
        } else {
            cart.push({ name: item, price: price, qty: 1 });
        }

        btn.innerHTML = "✔ Ordered";
        setTimeout(() => btn.innerHTML = "Order Now", 1200);

        if (cartVisible) showCartItems();
    });
});

// --- Quantity Functions ---
window.increaseQty = function (index) {
    cart[index].qty++;
    showCartItems();
};

window.decreaseQty = function (index) {
    if (cart[index].qty > 1) {
        cart[index].qty--;
    } else {
        cart.splice(index, 1);
    }
    showCartItems();
};
