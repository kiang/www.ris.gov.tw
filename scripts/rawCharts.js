const fs = require('fs');
const { ChartJSNodeCanvas } = require('chartjs-node-canvas');

const width = 1200; //px
const height = 900; //px
const canvasRenderService = new ChartJSNodeCanvas({ width, height });

(async () => {
    const birthDeath = require('../tmp/chart.json');
    const configuration = {
        type: 'line',
        data: birthDeath.data,
        options: {
            plugins: {
                title: {
                    display: true,
                    text: birthDeath.title
                }
            }
        },
        plugins: [
            {
                id: 'custom_canvas_background_color',
                beforeDraw: (chart) => {
                    const { ctx } = chart;
                    ctx.save();
                    ctx.globalCompositeOperation = 'destination-over';
                    ctx.fillStyle = 'white';
                    ctx.fillRect(0, 0, chart.width, chart.height);
                    ctx.restore();
                }
            }
        ],

    };

    const imageBuffer = await canvasRenderService.renderToBuffer(configuration);

    // Write image to file
    fs.writeFileSync(birthDeath.pngFilePath, imageBuffer);
})();