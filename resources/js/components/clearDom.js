document.addEventListener("DOMContentLoaded", () => setTimeout(() => {
       for (const sc of document.querySelectorAll("script")) sc.remove();
       for (const sc of document.querySelectorAll("link[rel='modulepreload']")) sc.remove();
   }, 1500)
);
