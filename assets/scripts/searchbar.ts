import { createApp } from "vue";

createApp({
    data() {
        return {
            timeout: null
        }
    },
   methods: {
        updateInput(event: KeyboardEvent) {
            clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                console.log(this.$refs.input.value);
            }, 1000)
        }
   }
}).mount('#search')