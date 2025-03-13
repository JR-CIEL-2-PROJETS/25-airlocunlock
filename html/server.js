const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');
const app = express();
const port = 3000;

app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

app.post('/send-confirmation', (req, res) => {
    const { name, email, checkin, checkout, guests } = req.body;

    let transporter = nodemailer.createTransport({
        service: 'gmail',
        auth: {
            user: 'airlockunlock@gmail.com', 
            pass: 'BtsCiel2025'   
        }
    });

    let mailOptions = {
        from: 'airlockunlock@gmail.com',
        to: email,
        subject: 'Confirmation de Réservation - AirLockUnlock',
        text: `Bonjour ${name},\n\nMerci d'avoir réservé avec AirLockUnlock. Voici les détails de votre réservation :\n\nDate d'arrivée : ${checkin}\nDate de départ : ${checkout}\nNombre de personnes : ${guests}\n\nNous espérons que vous passerez un agréable séjour !\n\nCordialement,\nL'équipe AirLockUnlock
        <img src="img/logo (2).png">
        `
    };

    transporter.sendMail(mailOptions, (error, info) => {
        if (error) {
            return console.log(error);
        }
        console.log('Email envoyé: ' + info.response);
        res.send('Email de confirmation envoyé avec succès !');
    });
});

app.listen(port, () => {
    console.log(`Serveur démarré sur http://localhost:${port}`);
});
