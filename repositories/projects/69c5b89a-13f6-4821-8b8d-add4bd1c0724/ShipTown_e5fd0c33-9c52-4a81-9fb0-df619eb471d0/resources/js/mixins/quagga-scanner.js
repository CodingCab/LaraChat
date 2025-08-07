import Quagga from 'quagga';

export default {

    initScanner(e) {
        this.showScanner = true;
        this.$nextTick(() => {
            Quagga.init({
                decoder : {
                    readers : [
                        "code_39_reader",
                        "ean_reader",
                        "code_128_reader",
                    ],
                    debug: {
                        drawBoundingBox: false,
                        showFrequency: false,
                        drawScanline: false,
                        showPattern: false
                    }
                },
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    area: { // defines rectangle of the detection/localization area
                        top: "0%",    // top offset
                        right: "0%",  // right offset
                        left: "0%",   // left offset
                        bottom: "0%"  // bottom offset
                    },
                    singleChannel: false // true: only the red color-channel is read
                }
            }, function(err) {
                if (err) {
                    console.log(err);
                    //return
                }
                console.log("Initialization finished. Ready to start");
                Quagga.start();
            });


            Quagga.onDetected((data) => {
                this.query = data.codeResult.code;
                this.updateUrlAndReloadProducts();
                this.stopScanner();
            })
        });
    },

    stopScanner() {
        this.showScanner = false;
        Quagga.stop();
    },

}
