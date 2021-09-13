var app = new Vue({
    el: "#app",
    delimiters: ["{(", ")}"],
    data: {
        preguntas: [],
        pActual: 0,
        resp_correctas: 0,
        ready: false,
        resultDB: 0,
    },
    created() {
        this.getPreguntas();
    },
    mounted() {
        this.ready = true;
    },
    computed: {
        progress: function () {
            var porcentaje = (this.pActual * 100) / this.preguntas.length;
            if (porcentaje > 100) {
                return 100;
            }
            return porcentaje;
        },
        result: function () {
            return (this.resp_correctas * 100) / this.preguntas.length;
        },
    },
    watch: {
        pActual: function (newval, oldval) {
            if (newval > this.preguntas.length) {
                this.insertResultadoEvaluacion();
            }
        },
    },
    methods: {
        getPreguntas: function () {
            let frm = new FormData();
            frm.append("request_type", "getPreguntasOpcionesEvaluacion");
            axios.post("api/ajax_controller.php", frm).then((res) => {
                this.preguntas = res.data.preguntas;
                this.resultDB = res.data.result;
            });
        },
        opcionMarcada: function (pregunta, opcion) {
            if (opcion.is_valid === "1") {
                this.resp_correctas += 1;
            }
        },
        insertResultadoEvaluacion: function () {
            let frm = new FormData();
            frm.append("request_type", "insertResultadoEvaluacion");
            frm.append("puntaje", this.result);
            frm.append("sesskey", sesskey);
            axios.post("api/ajax_controller.php", frm).then((res) => {
                console.log(res.data);
            });
        },
    },
});
