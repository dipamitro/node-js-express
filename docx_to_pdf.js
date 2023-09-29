const express = require('express');
const path = require('path');
const fs = require('fs').promises;
const multer = require('multer');
const libre = require('libreoffice-convert');
const ip = require('ip');

const app = express();

libre.convertAsync = require('util').promisify(libre.convert);

const storage = multer.diskStorage({
    destination: 'uploads/',
    filename: (req, file, cb) => {
        cb(null, file.originalname);
    },
});

const upload = multer({ storage });

app.use('/docx-to-pdf', upload.single('docxFile'), async (req, res) => {
    const ext = '.pdf';

    if (!req.file) {
        return res.status(400).send('File is required.');
    }

    const inputPath = req.file.path;
    const outputPath = path.join(__dirname, `/public/${req.file.originalname}${ext}`);

    try {
        const docxBuf = await fs.readFile(inputPath);

        const pdfBuf = await libre.convertAsync(docxBuf, ext, undefined);

        res.setHeader('Content-Disposition', `attachment; filename=${req.file.originalname}${ext}`);
        res.setHeader('Content-Type', 'application/pdf');

        res.writeHead(200);

        res.end(pdfBuf);
    } catch (err) {
        console.error('Error:', err);
        res.status(500).send('Internal Server Error');
    } finally {
        fs.unlink(inputPath, (err) => {
            if (err) {
                console.error('Error deleting uploaded file:', err);
            }
        });
    }
});

app.listen(4040, () => {
    console.log(`Example app listening on url http://` + ip.address() + `:` + 4040);
});
