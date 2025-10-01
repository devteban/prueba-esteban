import "./bootstrap";

import Alpine from "alpinejs";
import Sortable from "sortablejs";

window.Alpine = Alpine;
window.Sortable = Sortable;

// Helper function to check when Livewire is available
window.waitForLivewire = function (callback, maxAttempts = 50) {
    let attempts = 0;
    const checkLivewire = () => {
        if (typeof window.Livewire !== "undefined" && window.Livewire.find) {
            callback();
        } else if (attempts < maxAttempts) {
            attempts++;
            setTimeout(checkLivewire, 100);
        } else {
            console.warn(
                "Livewire did not load after",
                maxAttempts,
                "attempts"
            );
        }
    };
    checkLivewire();
};

Alpine.start();
