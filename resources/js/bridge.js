export let fromServer = null;
const fetchData = () => {
    const data = document.querySelector("meta[name='params']");
    const temp = JSON.parse(data.getAttribute("content") || "{}");
    data.remove();
    fromServer = temp;
    fromServer = Object.freeze(fromServer);
};

fetchData();

setTimeout(() => {
    console.log(fromServer);
}, 3000)
