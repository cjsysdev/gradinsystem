<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Units Demo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h2 {
            color: #2196f3;
            border-bottom: 2px solid #2196f3;
            padding-bottom: 5px;
        }

        .unit-demo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .box {
            background-color: #f1f1f1;
            text-align: center;
            padding: 10px;
            border-radius: 10px;
        }

        .px {
            width: 150px;
            height: 100px;
            font-size: 16px;
        }

        .em {
            width: 15em;
            height: 5em;
            font-size: 1.5em;
        }

        .rem {
            width: 15rem;
            height: 5rem;
            font-size: 1.2rem;
        }

        .vw {
            width: 20vw;
            height: 10vh;
            font-size: 2vw;
        }

        .vh {
            width: 20vh;
            height: 10vh;
            font-size: 2vh;
        }
    </style>
</head>

<body>

    <h1>CSS Units Example</h1>
    <p>This page demonstrates how <strong>px</strong>, <strong>em</strong>, <strong>rem</strong>, <strong>vw</strong>, and <strong>vh</strong> units affect element sizing.</p>

    <h2>Boxes with Different Units</h2>
    <div class="unit-demo">
        <div class="box px">150px</div>
        <div class="box em">15em</div>
        <div class="box rem">15rem</div>
        <div class="box vw">20vw</div>
        <div class="box vh">20vh</div>
    </div>

    <script>
        // Interactive: Toggle sizes dynamically
        const boxes = document.querySelectorAll('.box');
        boxes.forEach(box => {
            box.addEventListener('click', () => {
                box.style.backgroundColor = '#4CAF50';
                box.style.color = '#fff';
                box.textContent = 'Clicked!';
                setTimeout(() => {
                    box.style.backgroundColor = '#f1f1f1';
                    box.style.color = '#000';
                    box.textContent = box.className.split(' ')[1];
                }, 1000);
            });
        });
    </script>

</body>

</html>