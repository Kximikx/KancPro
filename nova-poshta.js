document.addEventListener("DOMContentLoaded", () => {
  // API ключ Нової пошти
  const apiKey = "YOUR_API_KEY" // Замініть на ваш API ключ

  // Елементи форми
  const citySearch = document.getElementById("np_city_search")
  const citySelect = document.getElementById("np_city")
  const warehouseSelect = document.getElementById("np_warehouse")
  const deliveryTypeRadios = document.getElementsByName("delivery_type")
  const warehouseGroup = document.getElementById("warehouse_group")
  const addressGroup = document.getElementById("address_group")
  const customerAddressField = document.getElementById("customer_address")
  const checkoutForm = document.getElementById("checkoutForm")

  // Обробка зміни типу доставки
  for (const radio of deliveryTypeRadios) {
    radio.addEventListener("change", function () {
      if (this.value === "warehouse") {
        warehouseGroup.style.display = "block"
        addressGroup.style.display = "none"
      } else {
        warehouseGroup.style.display = "none"
        addressGroup.style.display = "block"
      }
    })
  }

  // Пошук міст при введенні тексту
  let searchTimeout
  citySearch.addEventListener("input", function () {
    clearTimeout(searchTimeout)
    const searchText = this.value.trim()

    if (searchText.length < 2) return

    searchTimeout = setTimeout(() => {
      searchCities(searchText)
    }, 500)
  })

  // Завантаження відділень при виборі міста
  citySelect.addEventListener("change", function () {
    const cityRef = this.value
    if (cityRef) {
      loadWarehouses(cityRef)
    } else {
      // Очистити список відділень, якщо місто не вибрано
      warehouseSelect.innerHTML = '<option value="">Спочатку виберіть місто</option>'
    }
  })

  // Функція для пошуку міст
  function searchCities(searchText) {
    const requestData = {
      apiKey: apiKey,
      modelName: "Address",
      calledMethod: "searchSettlements",
      methodProperties: {
        CityName: searchText,
        Limit: 20,
      },
    }

    fetch("https://api.novaposhta.ua/v2.0/json/", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(requestData),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data && data.data[0] && data.data[0].Addresses) {
          // Очистити поточний список
          citySelect.innerHTML = '<option value="">Виберіть місто</option>'

          // Додати міста до списку
          data.data[0].Addresses.forEach((city) => {
            const option = document.createElement("option")
            option.value = city.DeliveryCity
            option.textContent = `${city.Present} (${city.Area})`
            citySelect.appendChild(option)
          })
        } else {
          citySelect.innerHTML = '<option value="">Міста не знайдено</option>'
        }
      })
      .catch((error) => {
        console.error("Помилка при пошуку міст:", error)
        citySelect.innerHTML = '<option value="">Помилка завантаження</option>'
      })
  }

  // Функція для завантаження відділень
  function loadWarehouses(cityRef) {
    const requestData = {
      apiKey: apiKey,
      modelName: "AddressGeneral",
      calledMethod: "getWarehouses",
      methodProperties: {
        CityRef: cityRef,
        Limit: 100,
      },
    }

    fetch("https://api.novaposhta.ua/v2.0/json/", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(requestData),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success && data.data) {
          // Очистити поточний список
          warehouseSelect.innerHTML = '<option value="">Виберіть відділення</option>'

          // Додати відділення до списку
          data.data.forEach((warehouse) => {
            const option = document.createElement("option")
            option.value = warehouse.Ref
            option.textContent = warehouse.Description
            warehouseSelect.appendChild(option)
          })
        } else {
          warehouseSelect.innerHTML = '<option value="">Відділення не знайдено</option>'
        }
      })
      .catch((error) => {
        console.error("Помилка при завантаженні відділень:", error)
        warehouseSelect.innerHTML = '<option value="">Помилка завантаження</option>'
      })
  }

  // Обробка відправки форми
  checkoutForm.addEventListener("submit", (e) => {
    // Формування повної адреси для збереження
    let fullAddress = ""
    const cityText = citySelect.options[citySelect.selectedIndex]?.textContent || ""

    if (document.querySelector('input[name="delivery_type"]:checked').value === "warehouse") {
      const warehouseText = warehouseSelect.options[warehouseSelect.selectedIndex]?.textContent || ""
      fullAddress = `${cityText}, ${warehouseText}`
    } else {
      const street = document.getElementById("np_street").value
      const building = document.getElementById("np_building").value
      const apartment = document.getElementById("np_apartment").value
      fullAddress = `${cityText}, вул. ${street}, буд. ${building}${apartment ? ", кв. " + apartment : ""}`
    }

    // Записуємо повну адресу в приховане поле
    customerAddressField.value = fullAddress
  })
})
