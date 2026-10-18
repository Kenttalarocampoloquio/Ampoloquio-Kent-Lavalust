const status = document.getElementById("status");
const serviceWorkerStatus = document.getElementById("serviceWorkerStatus");
const testButton = document.getElementById("testButton");
const message = document.getElementById("message");

const updateConnectionStatus = () => {
  const isOnline = navigator.onLine;
  status.textContent = isOnline ? "You are online." : "You are offline.";
  status.classList.toggle("online", isOnline);
  status.classList.toggle("offline", !isOnline);
};
updateConnectionStatus();
window.addEventListener("online", updateConnectionStatus);
window.addEventListener("offline", updateConnectionStatus);

if ("serviceWorker" in navigator) {
  navigator.serviceWorker
    .register("./service-worker.js")
    .then((registration) => {
      console.log("Service Worker registered:", registration.scope);
      serviceWorkerStatus.textContent = "Service Worker registered successfully!";
    })
    .catch((error) => {
      console.error("Registration failed:", error);
      serviceWorkerStatus.textContent = "Service Worker registration failed.";
    });
} else {
  serviceWorkerStatus.textContent = "Service Workers are not supported.";
}

testButton.addEventListener("click", () => {
  message.textContent = "JavaScript is working correctly!";
});