document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileMenuBtn = document.querySelector(".mobile-menu-btn")
  const menu = document.querySelector(".menu")

  mobileMenuBtn.addEventListener("click", () => {
    menu.classList.toggle("active")
  })

  // Product filtering
  const filterBtns = document.querySelectorAll(".filter-btn")
  const productCards = document.querySelectorAll(".product-card")

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Remove active class from all buttons
      filterBtns.forEach((btn) => btn.classList.remove("active"))

      // Add active class to clicked button
      this.classList.add("active")

      const filter = this.getAttribute("data-filter")

      productCards.forEach((card) => {
        if (filter === "all") {
          card.style.display = "block"
        } else if (card.getAttribute("data-category") === filter) {
          card.style.display = "block"
        } else {
          card.style.display = "none"
        }
      })
    })
  })

  // Shopping cart functionality
  const cartIcon = document.querySelector(".cart")
  const cartModal = document.getElementById("cartModal")
  const closeCart = document.querySelector(".close-cart")
  const cartItems = document.querySelector(".cart-items")
  const totalAmount = document.getElementById("totalAmount")
  const cartCount = document.querySelector(".cart-count")
  const addToCartBtns = document.querySelectorAll(".add-to-cart-btn")
  const checkoutBtn = document.querySelector(".checkout-btn")
  const checkoutModal = document.getElementById("checkoutModal")
  const closeCheckout = document.querySelector(".close-checkout")
  const checkoutForm = document.getElementById("checkoutForm")

  let cart = []

  // Load cart from localStorage if available
  if (localStorage.getItem("cart")) {
    try {
      cart = JSON.parse(localStorage.getItem("cart"))
      updateCart()
    } catch (e) {
      console.error("Error loading cart from localStorage:", e)
      localStorage.removeItem("cart")
    }
  }

  // Open cart modal
  cartIcon.addEventListener("click", () => {
    cartModal.style.display = "flex"
  })

  // Close cart modal
  closeCart.addEventListener("click", () => {
    cartModal.style.display = "none"
  })

  // Close cart when clicking outside
  window.addEventListener("click", (event) => {
    if (event.target === cartModal) {
      cartModal.style.display = "none"
    }
    if (event.target === checkoutModal) {
      checkoutModal.style.display = "none"
    }
  })

  // Close checkout modal
  if (closeCheckout) {
    closeCheckout.addEventListener("click", () => {
      checkoutModal.style.display = "none"
    })
  }

  // Add to cart functionality
  addToCartBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const productId = this.getAttribute("data-id")
      const productName = this.getAttribute("data-name")
      const productPrice = Number.parseFloat(this.getAttribute("data-price"))
      const productImg = this.getAttribute("data-image")

      // Check if product already in cart
      const existingItem = cart.find((item) => item.id === productId)

      if (existingItem) {
        existingItem.quantity++
      } else {
        cart.push({
          id: productId,
          name: productName,
          price: productPrice,
          img: productImg,
          quantity: 1,
        })
      }

      // Save cart to localStorage
      localStorage.setItem("cart", JSON.stringify(cart))

      updateCart()

      // Show notification
      showNotification(`${productName} додано до кошика!`)
    })
  })

  // Update cart function
  function updateCart() {
    // Clear cart items
    cartItems.innerHTML = ""

    // Update cart count
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0)
    cartCount.textContent = totalItems

    if (cart.length === 0) {
      cartItems.innerHTML = "<p>Ваш кошик порожній</p>"
      totalAmount.textContent = "0"
      return
    }

    // Add items to cart
    let total = 0

    cart.forEach((item, index) => {
      const cartItem = document.createElement("div")
      cartItem.classList.add("cart-item")

      // Перевірка наявності зображення та додавання обробника помилок
      const imgSrc = item.img || "uploads/no-image.png"

      cartItem.innerHTML = `
                <img src="${imgSrc}" alt="${item.name}" class="cart-item-img" onerror="this.onerror=null; this.src='uploads/no-image.png'">
                <div class="cart-item-details">
                    <h4 class="cart-item-title">${item.name}</h4>
                    <p class="cart-item-price">${item.price.toFixed(2)} грн</p>
                </div>
                <div class="cart-item-quantity">
                    <button class="quantity-btn minus" data-index="${index}">-</button>
                    <span class="quantity-value">${item.quantity}</span>
                    <button class="quantity-btn plus" data-index="${index}">+</button>
                </div>
                <div class="remove-item" data-index="${index}">
                    <i class="fas fa-trash"></i>
                </div>
            `

      cartItems.appendChild(cartItem)

      total += item.price * item.quantity
    })

    // Update total
    totalAmount.textContent = total.toFixed(2)

    // Add event listeners to quantity buttons
    document.querySelectorAll(".quantity-btn.minus").forEach((btn) => {
      btn.addEventListener("click", function () {
        const index = Number.parseInt(this.getAttribute("data-index"))
        if (cart[index].quantity > 1) {
          cart[index].quantity--
          localStorage.setItem("cart", JSON.stringify(cart))
          updateCart()
        }
      })
    })

    document.querySelectorAll(".quantity-btn.plus").forEach((btn) => {
      btn.addEventListener("click", function () {
        const index = Number.parseInt(this.getAttribute("data-index"))
        cart[index].quantity++
        localStorage.setItem("cart", JSON.stringify(cart))
        updateCart()
      })
    })

    // Add event listeners to remove buttons
    document.querySelectorAll(".remove-item").forEach((btn) => {
      btn.addEventListener("click", function () {
        const index = Number.parseInt(this.getAttribute("data-index"))
        cart.splice(index, 1)
        localStorage.setItem("cart", JSON.stringify(cart))
        updateCart()
      })
    })
  }

  // Checkout button
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", () => {
      if (cart.length === 0) {
        showNotification("Ваш кошик порожній!")
        return
      }

      cartModal.style.display = "none"
      checkoutModal.style.display = "flex"
    })
  }

  // Checkout form submission
  if (checkoutForm) {
    checkoutForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      const orderData = {
        customer_name: document.getElementById("customer_name").value,
        customer_email: document.getElementById("customer_email").value,
        customer_phone: document.getElementById("customer_phone").value,
        customer_address: document.getElementById("customer_address").value,
        items: cart.map((item) => ({
          id: item.id,
          quantity: item.quantity,
        })),
      }

      try {
        const response = await fetch("api/order.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(orderData),
        })

        const result = await response.json()

        if (result.success) {
          showNotification("Дякуємо за замовлення! Ми зв'яжемося з вами найближчим часом.")
          cart = []
          localStorage.removeItem("cart")
          updateCart()
          checkoutModal.style.display = "none"
          checkoutForm.reset()
        } else {
          showNotification("Помилка: " + result.message)
        }
      } catch (error) {
        console.error("Error submitting order:", error)
        showNotification("Сталася помилка при оформленні замовлення. Спробуйте пізніше.")
      }
    })
  }

  // Contact form submission
  const contactForm = document.getElementById("contactForm")
  if (contactForm) {
    contactForm.addEventListener("submit", (e) => {
      e.preventDefault()

      const name = document.getElementById("name").value
      const email = document.getElementById("email").value
      const subject = document.getElementById("subject").value
      const message = document.getElementById("message").value

      // Here you would normally send the form data to a server
      console.log("Form submitted:", { name, email, subject, message })

      // Reset form
      contactForm.reset()

      // Show notification
      showNotification("Повідомлення надіслано! Ми зв'яжемося з вами найближчим часом.")
    })
  }

  // Newsletter form submission
  const newsletterForm = document.getElementById("newsletterForm")
  if (newsletterForm) {
    newsletterForm.addEventListener("submit", function (e) {
      e.preventDefault()

      const email = this.querySelector('input[type="email"]').value

      // Here you would normally send the email to a server
      console.log("Newsletter subscription:", email)

      // Reset form
      newsletterForm.reset()

      // Show notification
      showNotification("Дякуємо за підписку на нашу розсилку!")
    })
  }

  // Notification function
  function showNotification(message) {
    const notification = document.createElement("div")
    notification.classList.add("notification")
    notification.textContent = message

    // Add styles to notification
    notification.style.position = "fixed"
    notification.style.bottom = "20px"
    notification.style.right = "20px"
    notification.style.backgroundColor = "#3498db"
    notification.style.color = "#fff"
    notification.style.padding = "15px 20px"
    notification.style.borderRadius = "4px"
    notification.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.1)"
    notification.style.zIndex = "9999"
    notification.style.opacity = "0"
    notification.style.transform = "translateY(20px)"
    notification.style.transition = "opacity 0.3s ease, transform 0.3s ease"

    document.body.appendChild(notification)

    // Show notification
    setTimeout(() => {
      notification.style.opacity = "1"
      notification.style.transform = "translateY(0)"
    }, 10)

    // Hide and remove notification after 3 seconds
    setTimeout(() => {
      notification.style.opacity = "0"
      notification.style.transform = "translateY(20px)"

      setTimeout(() => {
        document.body.removeChild(notification)
      }, 300)
    }, 3000)
  }

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()

      const target = document.querySelector(this.getAttribute("href"))

      if (target) {
        window.scrollTo({
          top: target.offsetTop - 80,
          behavior: "smooth",
        })

        // Close mobile menu if open
        menu.classList.remove("active")
      }
    })
  })

  // Add animation on scroll
  const animateOnScroll = () => {
    const elements = document.querySelectorAll(".category-card, .product-card, .about-content, .contact-content")

    elements.forEach((element) => {
      const elementPosition = element.getBoundingClientRect().top
      const screenPosition = window.innerHeight / 1.3

      if (elementPosition < screenPosition) {
        element.style.opacity = "1"
        element.style.transform = "translateY(0)"
      }
    })
  }

  // Set initial state for animated elements
  document.querySelectorAll(".category-card, .product-card, .about-content, .contact-content").forEach((element) => {
    element.style.opacity = "0"
    element.style.transform = "translateY(30px)"
    element.style.transition = "all 0.6s ease"
  })

  // Run animation on page load and scroll
  window.addEventListener("load", animateOnScroll)
  window.addEventListener("scroll", animateOnScroll)

  // Category cards click event
  document.querySelectorAll(".category-card").forEach((card) => {
    card.addEventListener("click", function () {
      const categoryId = this.getAttribute("data-category-id")
      const filterBtn = document.querySelector(`.filter-btn[data-filter="${categoryId}"]`)

      if (filterBtn) {
        // Scroll to products section
        const productsSection = document.querySelector("#products")
        window.scrollTo({
          top: productsSection.offsetTop - 80,
          behavior: "smooth",
        })

        // Trigger click on the corresponding filter button
        setTimeout(() => {
          filterBtn.click()
        }, 500)
      }
    })
  })

  // Додавання обробників помилок для всіх зображень
  document.querySelectorAll("img").forEach((img) => {
    img.onerror = function () {
      this.onerror = null
      this.src = "uploads/no-image.png"
    }
  })
})
