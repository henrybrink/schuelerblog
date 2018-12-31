<template>
    <div class="button-container">
        <h1>Ein test</h1>
        <button class="ui ${color} button" v-on:click="execute()">{{ title }}</button>
    </div>
</template>

<script>

    export default {
        name: 'ajax-button',
        props: {
            color: {
                type: String,
                default: 'primary'
            },
            title: {
              type: String,
              default: 'Button'
            },
            url: {
                type: String,
                default: 'http://localhost:1234/localhost'
            },
            object: {
                type: String,
                default: 'test'
            }
        },

        methods: {
            execute : function() {
                $.ajax(this.url, {
                    method: 'GET',
                    error: function() {
                        console.log("Es gab einen Fehler beim ausführen der Anfrage")
                    },
                    success: function() {
                      $('.ui.container').append('<div class="ui positive message">'+  +'</div>')
                    }
                });
            }
        }
    }
</script>