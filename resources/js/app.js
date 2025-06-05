import './bootstrap';

import Alpine from 'alpinejs';

import collapse from '@alpinejs/collapse';

import Chart from 'chart.js/auto';

window.Alpine = Alpine;

Alpine.start();

window.Chart = Chart;

document.addEventListener('DOMContentLoaded', function () {

    const showToast = (message, type = 'success') => {
        // Replace this with your actual toast notification system
        // Example with console.log, but you'd use Toastr, Noty, custom Alpine toast, etc.
        console.log(`Toast (${type}): ${message}`);
        // if (type === 'success') toastr.success(message);
        // else if (type === 'error') toastr.error(message);
        // else toastr.info(message);
    };

    const handleAjaxFormSubmit = (form, formType) => {
        event.preventDefault();
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) return;

        let originalButtonContent = '';
        const cartButtonTextContainer = submitButton.querySelector('.cart-button-text-container'); // For big card text
        const cartButtonText = submitButton.querySelector('.cart-button-text'); // For big card text span
        const icon = submitButton.querySelector(formType === 'cart' ? '.cart-icon' : '.wishlist-icon'); // General icon
        const iconFilled = submitButton.querySelector('.wishlist-icon-filled'); // Specific for wishlist
        const iconOutline = submitButton.querySelector('.wishlist-icon-outline'); // Specific for wishlist
        const spinner = submitButton.querySelector(formType === 'cart' ? '.cart-spinner' : '.wishlist-spinner');

        if (cartButtonText) {
            originalButtonContent = cartButtonText.innerHTML;
            cartButtonText.innerHTML = 'Adding...'; // Or just hide and show spinner
        } else if (icon) {
            originalButtonContent = icon.outerHTML; // Store original icon for restoration
            icon.classList.add('hidden');
            if (iconFilled) iconFilled.classList.add('hidden');
            if (iconOutline) iconOutline.classList.add('hidden');
        }
        if (spinner) spinner.classList.remove('hidden');
        submitButton.disabled = true;

        const formData = new FormData(form);
        const plainFormData = Object.fromEntries(formData.entries());
        const productId = form.dataset.productId; // Used by wishlist

        let actionUrl = form.action;
        const isCurrentlyInWishlist = formType === 'wishlist' && actionUrl.includes('/wishlist/remove/');

        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(plainFormData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errData => { errData.status = response.status; throw errData; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message || `${formType === 'cart' ? 'Item added to cart!' : 'Wishlist updated!'}`, 'success');

                if (formType === 'cart') {
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.cart_count } }));
                    if (cartButtonText) { // Big card
                        cartButtonText.innerHTML = 'Added!';
                        setTimeout(() => {
                            cartButtonText.innerHTML = originalButtonContent; // Restore original text "Add to Cart"
                            if (spinner) spinner.classList.add('hidden');
                            submitButton.disabled = false;
                        }, 2000);
                    } else if (iconContainer) { // Small card with icon
                         const cartIconContainer = submitButton.querySelector('.cart-button-icon-container');
                         if(cartIconContainer) {
                            const originalCartIconHTML = cartIconContainer.innerHTML; // Save state before changing
                            cartIconContainer.innerHTML = `<svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>`;
                            setTimeout(() => {
                                cartIconContainer.innerHTML = originalCartIconHTML; // Restore icons
                                if (spinner) spinner.classList.add('hidden'); // Ensure spinner is hidden
                                submitButton.disabled = false;
                            }, 2000);
                         }
                    }
                } else if (formType === 'wishlist') {
                    const newIsInWishlist = !isCurrentlyInWishlist;
                    form.action = newIsInWishlist
                        ? `{{ url('/wishlist/remove') }}/${productId}` // Ensure these URLs are correct
                        : `{{ url('/wishlist/add') }}/${productId}`;

                    if (iconFilled && iconOutline) {
                        iconFilled.classList.toggle('hidden', !newIsInWishlist);
                        iconOutline.classList.toggle('hidden', newIsInWishlist);
                    }
                    // Re-enable button and hide spinner
                    if (spinner) spinner.classList.add('hidden');
                    submitButton.disabled = false;
                }
            } else {
                showToast(data.message || `Could not ${formType === 'cart' ? 'add to cart' : 'update wishlist'}.`, 'error');
                // Restore original button state on failure
                if (cartButtonText) cartButtonText.innerHTML = originalButtonContent;
                else if (icon) {
                    icon.classList.remove('hidden'); // Show the original icon
                    if(isCurrentlyInWishlist && iconFilled) iconFilled.classList.remove('hidden');
                    else if(!isCurrentlyInWishlist && iconOutline) iconOutline.classList.remove('hidden');
                }
                if (spinner) spinner.classList.add('hidden');
                submitButton.disabled = false;
            }
        })
        .catch(error => {
            console.error(`AJAX Form (${formType}) Error:`, error);
            let errorMsg = 'An error occurred. Please try again.';
            if(error && error.message) errorMsg = error.message;
            if(error && error.errors) errorMsg += ': ' + Object.values(error.errors).flat().join('; ');
            showToast(errorMsg, 'error');

            // Restore original button state on catch
            if (cartButtonText) cartButtonText.innerHTML = originalButtonContent;
            else if (icon) {
                 icon.classList.remove('hidden'); // Show the original icon
                 if(isCurrentlyInWishlist && iconFilled) iconFilled.classList.remove('hidden');
                 else if(!isCurrentlyInWishlist && iconOutline) iconOutline.classList.remove('hidden');
            }
            if (spinner) spinner.classList.add('hidden');
            submitButton.disabled = false;
        });
    };

    // Attach to all cart forms
    document.querySelectorAll('form.ajax-cart-form').forEach(form => {
        form.addEventListener('submit', (event) => handleAjaxFormSubmit(event.target, 'cart'));
    });

    // Attach to all wishlist forms
    document.querySelectorAll('form.ajax-wishlist-form').forEach(form => {
        form.addEventListener('submit', (event) => handleAjaxFormSubmit(event.target, 'wishlist'));
    });

});