<?php
// Order.php
namespace Blinkfair\Form;

use TCPDF;

/*
 * The MIT License
 *
 * Copyright 2017 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
use Blinkfair\Init\Container;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
class OrderHandler extends AbstractFormHandler
{

    /**
     * Handle form request
     *
     * @return array
     */
    public function handle()
    {
        try {
            $this->checkFormToken();
        } catch (\Exception $e) {
            return [
                'type' => 'error',
                'errors' => [$e->getMessage()],
                'error_code' => 500
            ];
        }

        try {
            $errors = [];

            if (!$this->checkPostVariable('order_firstname')) {
                $errors[] = 'Vorname fehlt!';
            }

            if (!$this->checkPostVariable('order_lastname')) {
                $errors[] = 'Nachname fehlt!';
            }

            if (!$this->checkPostVariable('order_email')) {
                $errors[] = 'E-Mail-Adresse fehlt!';
            } else if (preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b./', $this->container->getGlobals()->getGlobal('post', 'order_email'))) {
                $errors[] = sprintf('Ungültige E-Mail-Adresse: %s', $this->container->getGlobals()->getGlobal('post', 'order_email'));
            }

            if (!$this->checkPostVariable('order_street')) {
                $errors[] = 'Straße fehlt!';
            }

            if (!$this->checkPostVariable('order_number')) {
                $errors[] = 'Hausnummer fehlt!';
            }

            if (!$this->checkPostVariable('order_plz')) {
                $errors[] = 'Postleitzahl fehlt!';
            } else if (!preg_match('|(\d){5}|', $this->container->getGlobals()->getGlobal('post', 'order_plz'))) {
                $errors[] = 'Ungültige Postleitzahl!';
            }

            $hasEspresso = false;
            $hasCoffee = false;

            if (!$this->checkPostVariable('order_products')) {
                $errors[] = 'Produkte fehlen!';
            } else if (isset($this->container->getGlobals()->getGlobals()['post']['order_products'])) {
                foreach ($this->container->getGlobals()->getGlobals()['post']['order_products'] as $product) {
                    if (!preg_match('/(coffee|espresso)/', $product)) {
                        $errors[] = sprintf('Ungültiges Produkt: %s', $product);
                    }

                    if ($product === 'coffee') {
                        $hasCoffee = true;
                    }

                    if ($product === 'espresso') {
                        $hasEspresso = true;
                    }
                }
            }

            if (!$hasEspresso && !$hasCoffee) {
                $errors[] = 'Ungültige Produktauswahl!';
            }

            if ($hasEspresso && !$this->checkPostVariable('order_espresso_amount')) {
                $errors[] = 'Espressoanzahl fehlt!';
            }

            if ($hasCoffee && !$this->checkPostVariable('order_coffee_amount')) {
                $errors[] = 'Kaffeeanzahl fehlt!';
            }

            if ($hasEspresso && !$this->checkPostVariable('order_espresso_mahlgrad')) {
                $errors[] = 'Mahlgrad fehlt!';
            } else if ($hasEspresso && !preg_match('/(ungemahlen|mocca|fein|mittel|grob)/', $this->container->getGlobals()->getGlobal('post', 'order_espresso_mahlgrad'))) {
                $errors[] = 'Ungültiger Mahlgrad!';
            }

            $hasOfficePost = false;
            if (!$this->checkPostVariable('order_ship')) {
                $errors[] = 'Versandmethode fehlt!';
            } else if (!preg_match('/(hermes|personal|officepost|self)/', $this->container->getGlobals()->getGlobal('post', 'order_ship'))) {
                $errors[] = 'Ungültige Verdandmethode!';
            } else {
                if ($this->container->getGlobals()->getGlobal('post', 'order_ship') === 'officepost') {
                    $hasOfficePost = true;
                }

                if ($this->checkPostVariable('order_plz') && $this->container->getGlobals()->getGlobal('post', 'order_ship') === 'personal'
                    && !preg_match('/(22549|22547|22559|22587|22589|22609)/', $this->container->getGlobals()->getGlobal('post', 'order_plz')))
                {
                    $errors[] = 'Adresse kann nicht persönlich beliefert werden.';
                }
            }

            if ($hasOfficePost && !$this->checkPostVariable('order_office_number')) {
                $errors[] = 'Behördennummer fehlt!';
            }

            if (!$this->checkPostVariable('order_pay')) {
                $errors[] = 'Bezahlmethode fehlt!';
            } else if (!preg_match('/(cash|transfer)/', $this->container->getGlobals()->getGlobal('post', 'order_pay'))) {
                $errors[] = 'Ungültige Bezahlmethode!';
            }

            if (count($errors) > 0) {
                return [
                    'type' => 'error',
                    'errors' => $errors
                ];
            } else {
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, 'UTF-8', false);

                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor('Blinkfair');
                $pdf->SetTitle('Rechnung');
                $pdf->SetSubject('Rechnung für Kaffeebestellung');
                $pdf->SetKeywords('Rehcnung, Kaffee, Blinkfair');

                // remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);

                $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
                $pdf->setFooterData(array(0,64,0), array(0,64,128));

                $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
                $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
                $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
                $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

                $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
                $pdf->setFontSubsetting(true);
                $pdf->SetFont('dejavusans', '', 14, '', true);
                $pdf->AddPage();
                $pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

                $products = [];
                $fullPrice = 0.0;

                foreach ($this->container->getGlobals()->getGlobals()['post']['order_products'] as $product) {
                    if ($product === 'coffee') {
                        $products[] = [
                            'name' => 'Kaffee',
                            'amount' => $this->container->getGlobals()->getGlobal('post', 'order_coffee_amount'),
                            'price' => (string)number_format($this->container->getGlobals()->getGlobal('post', 'order_coffee_amount') * 7, 2, ',', '.').'€'
                        ];

                        $fullPrice = $fullPrice + $this->container->getGlobals()->getGlobal('post', 'order_coffee_amount') * 7;
                    } else if ($product === 'espresso') {
                        $products[] = [
                            'name' => 'Espresso',
                            'amount' => $this->container->getGlobals()->getGlobal('post', 'order_espresso_amount'),
                            'price' => (string)number_format($this->container->getGlobals()->getGlobal('post', 'order_espresso_amount') * 8, 2, ',', '.').'€'
                        ];

                        $fullPrice = $fullPrice + $this->container->getGlobals()->getGlobal('post', 'order_espresso_amount') * 8;
                    }
                }

                if ($this->container->getGlobals()->getGlobal('post', 'order_ship') === 'hermes') {
                    $products[] = [
                        'name' => 'Versand mit Hermes',
                        'amount' => 1,
                        'price' => (string)number_format(4.5, 2, ',', '.').'€'
                    ];

                    $fullPrice = $fullPrice + 4.5;
                }

                $statement = $this->container->getPDO()->prepare('INSERT INTO orders 
                    (firstname, lastname, email, phone, street, number, plz, city, coffee_amount, 
                    espresso_amount, espresso_mahlgrad, ship, pay, office_number, comment)
                    VALUES (:order_firstname, :order_lastname, :order_email, :order_phone, 
                    :order_street, :order_number, :order_plz, :order_city, :order_coffee_amount,
                    :order_espresso_amount, :order_espresso_mahlgrad, :order_ship, :order_pay, :order_office_number, :order_comment)');

                if (!$hasEspresso) {
                    $this->container->getGlobals()->setGlobal('post', 'order_espresso_mahlgrad', 'none');
                    $this->container->getGlobals()->setGlobal('post', 'order_espresso_amount', 0);
                }

                if (!$hasCoffee) {
                    $this->container->getGlobals()->setGlobal('post', 'order_coffee_amount', 0);
                }

                if (!$this->checkPostVariable('order_phone')) {
                    $this->container->getGlobals()->setGlobal('post', 'order_phone', null);
                }

                $dbFields = ['order_firstname', 'order_lastname', 'order_email', 'order_phone', 'order_street', 'order_number',
                    'order_plz', 'order_city', 'order_coffee_amount', 'order_espresso_amount', 'order_espresso_mahlgrad', 'order_ship',
                    'order_pay', 'order_office_number', 'order_comment'];

                $params = [];

                $intFields = ['order_plz', 'order_coffee_amount', 'order_espresso_amount'];

                foreach ($this->container->getGlobals()->getGlobals()['post'] as $key => $val) {
                    if (in_array($key, $intFields)) {
                        $val = (integer)$val;
                    }
                    if (empty($val)) {
                        $val = null;
                    }

                    if (in_array($key, $dbFields)) {
                        $params[$key] = $val;
                    }
                }

                $statement->execute($params);
                $id = $this->container->getPDO()->lastInsertId();

                $shipMsg = [
                    'hermes' => 'Ihre Bestellung wird nach Zahlungseingang mit Hermes versendet.',
                    'personal' => 'Ihre Bestellung wird nach Zahlungseingang durch einen Mitarbeiter persönlich geliefert.',
                    'officepost' => 'Ihre Bestellung wird nach Zahlungseingang per Behördenpost an die angegebene Behördennummer versendet.',
                    'self' => 'Ihre Bestellung steht nach Zahlungseingang zur Selbstabholung bereit.'
                ];

                $shipTypes = [
                    'hermes' => 'Hermes',
                    'personal' => 'Persönlich',
                    'officepost' => 'Behördenpost',
                    'self' => 'Selbstabholung'
                ];

                $payTypes = [
                    'cash' => 'Barzahlung',
                    'transfer' => 'Überweisung'
                ];

                $payMsg = [
                    'cash' => 'Bitte geben Sie den angegebenen Betrag bei einem Blinkfair-Mitarbeiter ab.',
                    'transfer' => sprintf(
                        'Bitte überweisen Sie den angegebenen Betrag auf das folgende Konto:<br />
                                Bank: Haspa<br />
                                Kontoinhaber: Blinkfair Handelsgenossenschaft<br />
                                IBAN: DE19 1234 1234 1234 1234 12<br />
                                Verwendungszweck: Bestellung #%s<br />
                                <br />
                                Bitte geben Sie keine weiteren Daten im Verwendungzweck an, sonst können wir die Überweisung nicht zuordnen.', $id)
                ];

                $html = $this->container->getTemplate()->render('bill.html.twig', [
                    'firstname' => $this->container->getGlobals()->getGlobal('post', 'order_firstname'),
                    'lastname' => $this->container->getGlobals()->getGlobal('post', 'order_lastname'),
                    'street' => $this->container->getGlobals()->getGlobal('post', 'order_street'),
                    'number' => $this->container->getGlobals()->getGlobal('post', 'order_number'),
                    'plz' => $this->container->getGlobals()->getGlobal('post', 'order_plz'),
                    'city' => $this->container->getGlobals()->getGlobal('post', 'order_city'),
                    'products' => $products,
                    'full_price' => (string)number_format($fullPrice, 2, ',', '.').'€',
                    'ship' => $this->container->getGlobals()->getGlobal('post', 'order_ship'),
                    'ship_msg' => $shipMsg[$this->container->getGlobals()->getGlobal('post', 'order_ship')],
                    'ship_type' => $shipTypes[$this->container->getGlobals()->getGlobal('post', 'order_ship')],
                    'pay' => $this->container->getGlobals()->getGlobal('post', 'order_pay'),
                    'pay_type' => $payTypes[$this->container->getGlobals()->getGlobal('post', 'order_pay')],
                    'pay_msg' => $payMsg[$this->container->getGlobals()->getGlobal('post', 'order_pay')],
                    'date' => time(),
                    'office_number' => $hasOfficePost ? $this->container->getGlobals()->getGlobal('post', 'order_office_number') : null,
                    'espresso' => $hasEspresso,
                    'mahlgrad' => $hasEspresso ?  $this->container->getGlobals()->getGlobal('post', 'order_espresso_mahlgrad') : null
                ]);

                $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

                $pdfPath = sprintf('%s/order_bills/bill-%s.pdf', Container::ROOT_DIR, $id);
                $pdf->Output($pdfPath, 'F');

                $recipient = [$this->container->getGlobals()->getGlobal('post', 'order_email') => sprintf('%s %s',
                    $this->container->getGlobals()->getGlobal('post', 'order_firstname'),
                    $this->container->getGlobals()->getGlobal('post', 'order_lastname'))
                ];

                $attachment = \Swift_Attachment::fromPath($pdfPath);

                $message = (new \Swift_Message())
                    ->setSubject('Ihre Bestellung bei Blinkfair')
                    ->setFrom(['blinkfair@stsbl.de' => 'Blinkfair'])
                    ->setTo($recipient)
                    ->setBody($this->container->getTemplate()->render('mail.txt.twig', [
                        'firstname' => $this->container->getGlobals()->getGlobal('post', 'order_firstname'),
                        'lastname' => $this->container->getGlobals()->getGlobal('post', 'order_lastname'),
                    ]))
                    ->attach($attachment)
                ;

                $this->container->getMailer()->send($message);

                $message = (new \Swift_Message())
                    ->setSubject('Neue Bestellung bei Blinkfair')
                    ->setFrom(['blinkfair@stsbl.de' => 'Blinkfair'])
                    ->setTo(['felix.jacobi@stsbl.de' => 'Felix Jacobi'])
                    ->setBody($this->container->getTemplate()->render('intern-mail.txt.twig', $params))
                    ->attach($attachment)
                ;

                $this->container->getMailer()->send($message);

                $messages = [];

                $messages[] = 'Ihre Bestellung wurde erfolgreich aufgenommen. Die Rechnung wurde an die angegebene E-Mail-Adresse verschickt.';

                if ($this->container->getGlobals()->getGlobal('post', 'order_pay') === 'transfer') {
                    $messages[] = 'Die Überweisungsdaten finden Sie auf der Rechnung.';
                }

                return [
                    'type' => 'success',
                    'messages' => $messages
                ];
            }
        } catch (\Exception $e) {
            return [
                'type' => 'error',
                'errors' => [$e->getMessage()],
                'error_code' => 500
            ];
        }
    }
}