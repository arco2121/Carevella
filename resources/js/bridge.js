export let fromServer = null;
export let env = null;

export const fetchData = () => {
   try {
       const data = document.querySelector("meta[name='params']");
       const temp = JSON.parse(data.getAttribute("content") || "{}");
       data.remove();
       fromServer = temp;
       fromServer = Object.freeze(fromServer);
   } catch(e) {
       console.error(e);
   }
};

export const fetchEnv = () => {
    try {
        const data = document.querySelector("meta[name='env']");
        const temp = JSON.parse(data.getAttribute("content") || "{}");
        data.remove();
        env = temp;
        env = Object.freeze(env);
    } catch(e) {
        console.error(e);
    }
};

fetchData();
fetchEnv();
