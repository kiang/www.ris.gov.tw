const fs = require('fs');
const { ChartJSNodeCanvas } = require('chartjs-node-canvas');

const width = 1200; //px
const height = 900; //px
const canvasRenderService = new ChartJSNodeCanvas({ width, height });

(async () => {
    const configuration = {
        type: 'line',
        data: require('../docs/png/birth_death/chart.json'),
        options: {
            plugins: {
                title: {
                    display: true,
                    text: '出生死亡曲線圖'
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
    fs.writeFileSync('docs/png/birth_death/chart.png', imageBuffer);
})();