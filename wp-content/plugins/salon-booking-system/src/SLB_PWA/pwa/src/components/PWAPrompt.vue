<!-- PWAPrompt.vue -->
<template>
    <div>
        <b-alert show dismissible variant="secondary" class="add-to-home-screen" v-if="showIOS">
            {{ $t('installPWAIOSText') }}
        </b-alert>
        <b-alert :show="shown" dismissible variant="secondary" class="add-to-home-screen" v-else>
            <span class="logo"><img src="./../assets/logo.png" /></span>
            <span class="text">{{ $t('installPWAPromptText') }}</span>
            <b-button @click="installPWA" class="btn-install">
                {{ $t('installPWAPromptInstallBtnLabel') }}
            </b-button>
            <b-button @click="dismissPrompt">
                {{ $t('installPWAPromptNoInstallBtnLabel') }}
            </b-button>
        </b-alert>
    </div>
</template>

<script>
    export default {
        data: () => ({
            shown: false,
            showIOS: false,
        }),
        beforeMount() {
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault()
                this.installEvent = e
                this.shown = true
            })
            const isIos = () => {
                const userAgent = window.navigator.userAgent.toLowerCase();
                return /iphone|ipad|ipod/.test( userAgent );
            }
            // Detects if device is in standalone mode
            const isInStandaloneMode = () => ('standalone' in window.navigator) && (window.navigator.standalone);

            // Checks if should display install popup notification:
            if (isIos() && !isInStandaloneMode()) {
                this.showIOS = true;
            }
        },
        methods: {
            dismissPrompt() {
                this.shown = false
            },
            installPWA() {
                this.installEvent.prompt()
                this.installEvent.userChoice.then((choice) => {
                    this.dismissPrompt() // Hide the prompt once the user's clicked
                    if (choice.outcome === 'accepted') {
                        // Do something additional if the user chose to install
                    } else {
                        // Do something additional if the user declined
                    }
                })
            },
        }
    }
</script>

<style scoped>
    .add-to-home-screen {
        position: fixed;
        bottom: 36px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .text {
        margin-right: 10px;
        font-weight: bold;
    }
    .btn-install {
        margin-right: 5px;
    }
    .logo img {
        width: 50px;
        margin-right: 10px;
    }
</style>