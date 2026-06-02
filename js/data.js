// PHP Dojo — SNIPPETS + QUIZZES data
// Each topic has: desc, s1{label,code}, s2{label,code}
// eslint-disable-next-line no-unused-vars
const SNIPPETS={
'variables-types':{
desc:'PHP is loosely typed \u2014 variables hold any type and can change at runtime. Use <code>gettype()</code>, <code>var_dump()</code>, and <code>is_*()</code> to inspect them.',
s1:{label:'Declaration & inspection',code:`<?php
$name    = "PHP";       // string
$version = 8.3;         // float
$active  = true;        // bool
$nothing = null;        // null

echo gettype($name);    // string
echo gettype($version); // double
var_dump($active);      // bool(true)
is_string($name);       // true
isset($nothing);        // false`},
s2:{label:'var_dump vs print_r vs var_export',code:`<?php
$data = ["lang" => "PHP", "v" => 8, "ok" => true];

var_dump($data);
// array(3) { ["lang"]=> string(3) "PHP" ... }

print_r($data);
// Array ( [lang] => PHP [v] => 8 [ok] => 1 )

var_export($data);
// array ( 'lang' => 'PHP', 'v' => 8, 'ok' => true )`}},

'type-casting':{
desc:'Cast between types with <code>(int)</code>, <code>(float)</code>, <code>(string)</code>, <code>(bool)</code>, <code>(array)</code>, <code>(object)</code>, or use <code>intval()</code> / <code>settype()</code>.',
s1:{label:'Cast operators',code:`<?php
$str = "42px";
echo (int)   $str;       // 42
echo (float) "3.14abc";  // 3.14
echo (bool)  "";         // false
echo (bool)  "0";        // false
echo (bool)  "hello";    // true
echo (string) true;      // "1"

$arr = (array) "hello";  // ["hello"]
$obj = (object) ['x' => 1]; // stdClass`},
s2:{label:'intval with base & settype',code:`<?php
echo intval("0x1A", 16); // 26
echo intval("0b1010", 2); // 10
echo intval("077", 8);   // 63

$val = "3.14";
settype($val, "float");
var_dump($val); // float(3.14)

echo intdiv(7, 2);  // 3`}},

'math':{
desc:'PHP\\'s built-in math library: rounding, powers, roots, random numbers, and integer division.',
s1:{label:'Common operations',code:`<?php
echo abs(-7);          // 7
echo ceil(4.1);        // 5
echo floor(4.9);       // 4
echo round(4.567, 2);  // 4.57
echo max(1, 5, 3);     // 5
echo min(1, 5, 3);     // 1
echo pow(2, 10);       // 1024
echo sqrt(144);        // 12
echo M_PI;             // 3.14159...`},
s2:{label:'Random & integer division',code:`<?php
echo intdiv(7, 3);     // 2
echo fmod(7.5, 2.0);   // 1.5
echo fdiv(1, 0);       // INF (no error)

$n = random_int(1, 100); // crypto-secure
echo number_format(1234567.89, 2, '.', ',');
// 1,234,567.89

echo base_convert("ff", 16, 10); // 255`}},

'strings':{
desc:'Over 100 string functions for searching, replacing, splitting, trimming, padding, formatting and encoding.',
s1:{label:'Search, replace, check',code:`<?php
$s = "  Hello World  ";
echo strlen($s);                // 15
echo trim($s);                  // "Hello World"
echo strtolower($s);            // "  hello world  "
echo str_replace("World","PHP",trim($s)); // "Hello PHP"
echo substr($s, 2, 5);         // "Hello"
echo strpos($s, "World");      // 8
echo str_contains($s, "Hello"); // true  (PHP 8.0)
echo str_starts_with($s, "  H"); // true`},
s2:{label:'Split, join, pad, format',code:`<?php
$parts = explode(",", "a,b,c"); // ["a","b","c"]
$joined = implode(" | ", $parts); // "a | b | c"

echo str_pad("42", 5, "0", STR_PAD_LEFT); // "00042"
echo str_repeat("ab", 3);                 // "ababab"
echo str_word_count("Hello World");        // 2

echo sprintf("%-10s %05d %.2f", "item", 7, 3.5);
// "item       00007 3.50"`}},

'output-buffering':{
desc:'Control output with <code>echo</code>, <code>print</code>, <code>printf</code>, <code>sprintf</code>, and the <code>ob_*()</code> output-buffering functions.',
s1:{label:'printf / sprintf formatting',code:`<?php
printf("Name: %-10s Age: %d\\n", "Alice", 30);
// Name: Alice      Age: 30

$line = sprintf("%08.3f", 3.14159);
echo $line; // 0003.142

echo number_format(1234.5, 2); // 1,234.50`},
s2:{label:'Output buffering',code:`<?php
ob_start();
echo "Hello from the buffer!";
$html = ob_get_clean(); // fetch + clear + stop

echo strlen($html); // 22

// Nested buffers
ob_start();
  echo "outer ";
  ob_start();
    echo "inner";
  $inner = ob_get_clean();
  echo $inner . " content";
$full = ob_get_clean();
echo $full; // "outer inner content"`}},

'arrays':{
desc:'PHP arrays are ordered maps: numerically or string-keyed. The standard library has 79+ array functions.',
s1:{label:'map, filter, reduce, column',code:`<?php
$prices = ['apple' => 1.2, 'banana' => 0.5, 'cherry' => 2.0];

$doubled = array_map(fn($p) => $p * 2, $prices);
$cheap = array_filter($prices, fn($p) => $p < 1.0);
$total = array_reduce($prices, fn($s, $p) => $s + $p, 0);

$users = [['id'=>1,'name'=>'Ana'],['id'=>2,'name'=>'Bob']];
$names = array_column($users, 'name');     // ['Ana','Bob']
$byId  = array_column($users, 'name','id'); // [1=>'Ana',2=>'Bob']`},
s2:{label:'Sorting & searching',code:`<?php
$nums = [3, 1, 4, 1, 5, 9, 2, 6];
sort($nums);          // [1,1,2,3,4,5,6,9]
usort($nums, fn($a,$b) => $b <=> $a); // desc

echo in_array(4, $nums) ? 'found' : 'not found';
$key = array_search(4, $nums);

$slice  = array_slice($nums, 2, 3);
$unique = array_unique([1,2,2,3,3,3]); // [1,2,3]
$merged = array_merge([1,2], [3,4]);   // [1,2,3,4]`}},

'arrayobject':{
desc:'<code>ArrayObject</code> wraps arrays as objects, supports <code>ArrayAccess</code>, <code>Countable</code> and <code>IteratorAggregate</code>.',
s1:{label:'Basic usage',code:`<?php
$ao = new ArrayObject(['x' => 10, 'y' => 20, 'z' => 30]);
$ao['w'] = 40;
unset($ao['x']);

echo $ao->count(); // 3
foreach ($ao as $k => $v) echo "$k: $v\\n";

$arr = $ao->getArrayCopy(); // back to array
$ao->setFlags(ArrayObject::ARRAY_AS_PROPS);
$ao->y = 99; // property-style access`},
s2:{label:'Custom typed collection',code:`<?php
class TypedCollection extends ArrayObject {
    public function __construct(private string $type) {
        parent::__construct([]);
    }
    public function offsetSet(mixed $key, mixed $value): void {
        if (!($value instanceof $this->type)) {
            throw new InvalidArgumentException(
                "Expected {$this->type}"
            );
        }
        parent::offsetSet($key, $value);
    }
}
$col = new TypedCollection(DateTime::class);
$col[] = new DateTime('2025-01-01');
echo $col->count(); // 1`}},

'closures':{
desc:'Anonymous functions, arrow functions (<code>fn =></code>), <code>use</code> bindings, currying, and higher-order functions.',
s1:{label:'Arrow functions & use',code:`<?php
$tax = 0.21;
$net = fn(float $price) => $price * (1 + $tax);
echo $net(100.0); // 121.0

$prefix = "Hello";
$greet  = function(string $name) use ($prefix): string {
    return "$prefix, $name!";
};
echo $greet("PHP"); // Hello, PHP!`},
s2:{label:'Currying & compose',code:`<?php
function multiply(int $factor): Closure {
    return fn(int $n) => $n * $factor;
}
$double = multiply(2);
echo $double(5); // 10

function compose(callable ...$fns): Closure {
    return function($x) use ($fns) {
        return array_reduce(
            array_reverse($fns),
            fn($v, $f) => $f($v), $x
        );
    };
}
$process = compose('strtoupper', 'trim');
echo $process("  hello  "); // "HELLO"`}},

'datetime':{
desc:'Prefer <code>DateTimeImmutable</code> over mutable <code>DateTime</code>. Use <code>DateInterval</code> for arithmetic.',
s1:{label:'Immutable dates & arithmetic',code:`<?php
$now      = new DateTimeImmutable();
$tomorrow = $now->modify('+1 day');
$nextWeek = $now->add(new DateInterval('P7D'));

echo $now->format('Y-m-d H:i:s');
echo $tomorrow->format('D, d M Y');

$start = new DateTimeImmutable('2026-01-01');
$diff  = $now->diff($start);
echo $diff->days . " days since Jan 1";`},
s2:{label:'Parsing & timezones',code:`<?php
$date = DateTimeImmutable::createFromFormat('d/m/Y', '25/12/2025');
echo $date->format('Y-m-d'); // 2025-12-25

$utc    = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$madrid = $utc->setTimezone(new DateTimeZone('Europe/Madrid'));
echo $madrid->format('H:i T');

$a = new DateTimeImmutable('2026-01-01');
$b = new DateTimeImmutable('2026-06-01');
echo ($b > $a) ? "b is later" : "a is later";`}},

'json':{
desc:'Encode PHP values to JSON and decode back. Always use <code>JSON_THROW_ON_ERROR</code> for safety.',
s1:{label:'Encode & decode',code:`<?php
$data = ['name' => 'Alice', 'age' => 30, 'tags' => ['php','dev']];

$json = json_encode($data, JSON_PRETTY_PRINT);
echo $json;

$arr = json_decode($json, associative: true);
echo $arr['name']; // Alice

$obj = json_decode($json);
echo $obj->name;  // Alice`},
s2:{label:'Error handling',code:`<?php
try {
    $data = json_decode('{"bad": json}', flags: JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo $e->getMessage(); // Syntax error
}

// Legacy check
$result = json_decode('invalid');
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_last_error_msg();
}`}},

'regex':{
desc:'PHP uses PCRE (Perl-Compatible Regular Expressions). Key: <code>preg_match</code>, <code>preg_match_all</code>, <code>preg_replace</code>, <code>preg_split</code>.',
s1:{label:'Match & capture',code:`<?php
$text = "Prices: \\$12.50 and \\$7.99";

preg_match('/\\\\\\$(\\d+\\.\\d{2})/', $text, $m);
echo $m[1]; // 12.50

preg_match_all('/\\\\\\$(\\d+\\.\\d{2})/', $text, $all);
print_r($all[1]); // ["12.50", "7.99"]

// Named captures
preg_match('/(?P<year>\\d{4})-(?P<month>\\d{2})/', '2026-06', $dt);
echo $dt['year'];  // 2026`},
s2:{label:'Replace & split',code:`<?php
$clean = preg_replace('/\\s+/', ' ', "too  many   spaces");
echo $clean; // "too many spaces"

$result = preg_replace_callback('/\\d+/', function($m) {
    return $m[0] * 2;
}, "a=3 b=7");
echo $result; // "a=6 b=14"

$tokens = preg_split('/[\\s,;]+/', 'a, b;  c d');
// ["a", "b", "c", "d"]`}},

'generators':{
desc:'Generators use <code>yield</code> to produce values lazily \u2014 ideal for large data sets or infinite sequences.',
s1:{label:'Infinite sequence',code:`<?php
function fibonacci(): Generator {
    [$a, $b] = [0, 1];
    while (true) {
        yield $a;
        [$a, $b] = [$b, $a + $b];
    }
}

$fib = fibonacci();
$out = [];
for ($i = 0; $i < 8; $i++) {
    $out[] = $fib->current();
    $fib->next();
}
echo implode(', ', $out); // 0, 1, 1, 2, 3, 5, 8, 13`},
s2:{label:'yield key => value & send',code:`<?php
function indexedSquares(int $n): Generator {
    for ($i = 1; $i <= $n; $i++) {
        yield "sq$i" => $i ** 2;
    }
}
foreach (indexedSquares(4) as $k => $v) {
    echo "$k=$v "; // sq1=1 sq2=4 sq3=9 sq4=16
}

// Two-way communication
function accumulator(): Generator {
    $total = 0;
    while (true) {
        $n = yield $total;
        if ($n === null) break;
        $total += $n;
    }
}`}},

'mbstring':{
desc:'For UTF-8 or multibyte encodings, use <code>mb_*</code> functions \u2014 they count characters, not bytes.',
s1:{label:'Length, case, substring',code:`<?php
$text = "H\\u00e9llo W\\u00f6rld";
echo strlen($text);      // 13 (bytes)
echo mb_strlen($text);   // 11 (chars)

echo mb_strtoupper($text);     // H\\u00c9LLO W\\u00d6RLD
echo mb_substr($text, 0, 5);  // H\\u00e9llo
echo mb_strpos($text, 'W');   // 6`},
s2:{label:'Encoding & split',code:`<?php
$enc = mb_detect_encoding($str, ['UTF-8', 'ISO-8859-1']);
echo $enc; // UTF-8

mb_internal_encoding('UTF-8');

$chars = mb_str_split("h\\u00e9llo"); // ["h","\\u00e9","l","l","o"]

echo mb_substr_count('d\\u00e9j\\u00e0 vu d\\u00e9j\\u00e0', 'd\\u00e9j\\u00e0'); // 2`}},

'oop':{
desc:'Classes, inheritance, interfaces, traits, constructor promotion, magic methods, and readonly properties.',
s1:{label:'Class, inheritance, interface',code:`<?php
interface Speakable {
    public function speak(): string;
}

abstract class Animal implements Speakable {
    public function __construct(
        protected readonly string $name,
        protected int $age,
    ) {}
}

class Dog extends Animal {
    public function speak(): string {
        return "{$this->name} barks!";
    }
}

$dog = new Dog("Rex", 3);
echo $dog->speak(); // Rex barks!`},
s2:{label:'Traits & magic methods',code:`<?php
trait Timestampable {
    private ?DateTimeImmutable $createdAt = null;
    public function touch(): void {
        $this->createdAt ??= new DateTimeImmutable();
    }
    public function getCreatedAt(): ?DateTimeImmutable {
        return $this->createdAt;
    }
}

class Post {
    use Timestampable;
    public function __construct(public string $title) {}
    public function __toString(): string { return $this->title; }
}

$p = new Post("Hello");
$p->touch();
echo $p; // Hello`}},

'namespaces':{
desc:'Namespaces prevent name collisions and are the foundation of PSR-4 autoloading.',
s1:{label:'Declare & use',code:`<?php
namespace App\\Services;

use App\\Models\\User;
use App\\Repositories\\UserRepository;
use RuntimeException;

class UserService {
    public function __construct(
        private readonly UserRepository $repo,
    ) {}

    public function getOrFail(int $id): User {
        return $this->repo->find($id)
            ?? throw new RuntimeException("User $id not found");
    }
}`},
s2:{label:'Aliases & function imports',code:`<?php
use App\\{Models\\User, Models\\Post, Services\\AuthService};

// Alias to avoid conflicts
use Monolog\\Logger as MonologLogger;
use App\\Logger;

// Import functions and constants
use function App\\Helpers\\sanitize;
use const App\\Config\\APP_KEY;

// Fully qualified (always works)
$dt = new \\DateTimeImmutable();`}},

'error-handling':{
desc:'PHP 8 unified errors and exceptions. Use typed exceptions, <code>finally</code>, and custom exception hierarchies.',
s1:{label:'try / catch / finally',code:`<?php
function divide(float $a, float $b): float {
    if ($b === 0.0) throw new DivisionByZeroError("Cannot divide by zero");
    return $a / $b;
}

try {
    echo divide(10, 0);
} catch (DivisionByZeroError $e) {
    echo "Math error: " . $e->getMessage();
} finally {
    echo "\\nAlways runs.";
}

// PHP 8: catch without variable
try {
    json_decode('bad', flags: JSON_THROW_ON_ERROR);
} catch (JsonException) {
    echo "JSON parse failed";
}`},
s2:{label:'Custom exception hierarchy',code:`<?php
class AppException extends RuntimeException {}
class NotFoundException extends AppException {}
class ValidationException extends AppException {
    public function __construct(
        private readonly array $errors,
        string $message = "Validation failed",
    ) { parent::__construct($message); }

    public function getErrors(): array { return $this->errors; }
}

set_exception_handler(function(Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit(json_encode(['error' => $e->getMessage()]));
});`}},

'filesystem':{
desc:'Read, write, copy, delete files and directories. Prefer <code>file_get_contents</code> / <code>file_put_contents</code> for simple I/O.',
s1:{label:'Read, write, info',code:`<?php
file_put_contents('/tmp/hello.txt', "Hello!\\n", FILE_APPEND);
$content = file_get_contents('/tmp/hello.txt');

echo file_exists('/tmp/hello.txt') ? 'exists' : 'missing';
echo filesize('/tmp/hello.txt');
echo realpath('/tmp/hello.txt');

$lines = file('/tmp/hello.txt', FILE_IGNORE_NEW_LINES);

$f = new SplFileObject('/tmp/hello.txt');
foreach ($f as $line) echo $line;`},
s2:{label:'Directory operations',code:`<?php
mkdir('/tmp/mydir/sub', 0755, recursive: true);
$phpFiles = glob('/tmp/*.php');

$in  = fopen('/tmp/big.csv', 'r');
$out = fopen('/tmp/out.csv', 'w');
while (!feof($in)) {
    $row = fgetcsv($in);
    if ($row) fputcsv($out, array_map('strtoupper', $row));
}
fclose($in);
fclose($out);

copy('/tmp/hello.txt', '/tmp/hello.bak');
unlink('/tmp/hello.bak');`}},

'http':{
desc:'Control HTTP responses with <code>header()</code>. Read requests via super-globals and <code>php://input</code>.',
s1:{label:'Response headers & codes',code:`<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
http_response_code(201);

// Redirect
header('Location: /login', true, 302);
exit;

if (!headers_sent()) {
    header('X-Custom: value');
}`},
s2:{label:'Reading requests',code:`<?php
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];
$page   = (int) ($_GET['page'] ?? 1);

// JSON body (REST APIs)
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

// Auth header
$auth  = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $auth);

$file = $_FILES['upload'] ?? null;`}},

'curl':{
desc:'PHP\\'s cURL extension wraps libcurl for HTTP requests, auth, redirects, and file transfers.',
s1:{label:'GET request',code:`<?php
$ch = curl_init('https://api.example.com/users');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

if ($status === 200) $users = json_decode($response, true);`},
s2:{label:'POST with JSON body',code:`<?php
$payload = json_encode(['name' => 'Alice', 'role' => 'admin']);

$ch = curl_init('https://api.example.com/users');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload),
    ],
]);

$res = curl_exec($ch);
curl_close($ch);`}},

'encryption':{
desc:'Password hashing with <code>password_hash</code> (Argon2id), symmetric encryption with OpenSSL, HMAC signatures.',
s1:{label:'Password hashing',code:`<?php
$hash = password_hash('s3cr3t!', PASSWORD_ARGON2ID, [
    'memory_cost' => 65536,
    'time_cost'   => 4,
    'threads'     => 3,
]);

$ok = password_verify('s3cr3t!', $hash); // true

if (password_needs_rehash($hash, PASSWORD_ARGON2ID)) {
    $hash = password_hash('s3cr3t!', PASSWORD_ARGON2ID);
}

$bytes = random_bytes(32);
$int   = random_int(1, 100);`},
s2:{label:'AES-256 & HMAC',code:`<?php
$key = random_bytes(32);
$iv  = random_bytes(16);

$ciphertext = openssl_encrypt('Top secret', 'AES-256-CBC', $key, iv: $iv);
$decrypted  = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, iv: $iv);

// Store IV alongside ciphertext
$stored = base64_encode($iv) . ':' . $ciphertext;

// HMAC signature
$sig   = hash_hmac('sha256', 'payload', $key);
$valid = hash_equals($sig, hash_hmac('sha256', 'payload', $key));`}},

'xml':{
desc:'Parse and generate XML with <code>SimpleXML</code> (easy) or <code>DOMDocument</code> (full control).',
s1:{label:'SimpleXML parsing',code:`<?php
$xml = simplexml_load_string('<catalog>
  <book id="1" lang="en"><title>Clean Code</title><price>35</price></book>
  <book id="2" lang="es"><title>PHP 8</title><price>29.99</price></book>
</catalog>');

foreach ($xml->book as $book) {
    echo (string)$book['id'] . ': ' . (string)$book->title . "\\n";
}

$english = $xml->xpath('//book[@lang="en"]/title');
echo (string)$english[0]; // Clean Code`},
s2:{label:'DOMDocument creation',code:`<?php
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;
$root = $dom->createElement('users');
$dom->appendChild($root);

foreach ([['id'=>1,'name'=>'Alice'],['id'=>2,'name'=>'Bob']] as $u) {
    $user = $dom->createElement('user');
    $user->setAttribute('id', (string)$u['id']);
    $user->appendChild($dom->createElement('name', $u['name']));
    $root->appendChild($user);
}

echo $dom->saveXML();`}},

'database-pdo':{
desc:'PDO is the standard database abstraction. Always use prepared statements. Use transactions for atomicity.',
s1:{label:'Connect & query safely',code:`<?php
$pdo = new PDO(
    'mysql:host=localhost;dbname=app;charset=utf8mb4',
    'root', 'secret',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES => false]
);

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute(['alice@example.com']);
$user = $stmt->fetch();

$pdo->prepare('INSERT INTO users (name) VALUES (:n)')
    ->execute([':n' => 'Alice']);
$newId = (int)$pdo->lastInsertId();`},
s2:{label:'Transactions',code:`<?php
$pdo->beginTransaction();
try {
    $pdo->prepare('UPDATE accounts SET balance = balance - ? WHERE id = ?')
        ->execute([100, $fromId]);
    $pdo->prepare('UPDATE accounts SET balance = balance + ? WHERE id = ?')
        ->execute([100, $toId]);
    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    throw $e;
}

$count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

$sqlite = new PDO('sqlite::memory:');`}},

'design-patterns':{
desc:'Common GoF and enterprise patterns in PHP: Singleton, Factory, Repository, Strategy, Observer.',
s1:{label:'Singleton & Factory',code:`<?php
class Registry {
    private static ?self $inst = null;
    private array $data = [];
    private function __construct() {}
    public static function getInstance(): self {
        return self::$inst ??= new self();
    }
    public function set(string $k, mixed $v): void { $this->data[$k] = $v; }
    public function get(string $k): mixed { return $this->data[$k] ?? null; }
}

interface Logger { public function log(string $msg): void; }
class FileLogger implements Logger {
    public function log(string $m): void {
        file_put_contents('app.log', $m.PHP_EOL, FILE_APPEND);
    }
}`},
s2:{label:'Strategy pattern',code:`<?php
interface SortStrategy {
    public function sort(array &$data): void;
}

class BubbleSort implements SortStrategy {
    public function sort(array &$data): void {
        $n = count($data);
        for ($i = 0; $i < $n-1; $i++)
            for ($j = 0; $j < $n-$i-1; $j++)
                if ($data[$j] > $data[$j+1])
                    [$data[$j], $data[$j+1]] = [$data[$j+1], $data[$j]];
    }
}

class Sorter {
    public function __construct(private SortStrategy $s) {}
    public function run(array &$d): void { $this->s->sort($d); }
}`}},

'php8':{
desc:'PHP 8.0\u20138.3: named arguments, match, nullsafe, enums, readonly, fibers, union/intersection types.',
s1:{label:'Match, named args, nullsafe',code:`<?php
$status = 2;
$label = match($status) {
    1       => 'Active',
    2, 3    => 'Pending',
    default => 'Unknown',
};

$arr = array_slice(array: [1,2,3,4,5], offset: 1, length: 3);

$city = $user?->getAddress()?->getCity() ?? 'Unknown';

$val = $input ?? throw new InvalidArgumentException("required");`},
s2:{label:'Enums, readonly, fibers',code:`<?php
enum Status: string {
    case Active   = 'active';
    case Pending  = 'pending';
    case Archived = 'archived';

    public function label(): string {
        return match($this) {
            self::Active => 'Active',
            self::Pending => 'Pending review',
            self::Archived => 'Archived',
        };
    }
}
echo Status::Active->value; // active

class Point {
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {}
}

$fiber = new Fiber(function(): void {
    $val = Fiber::suspend('first');
    echo "Got: $val";
});
echo $fiber->start();    // "first"
$fiber->resume('hello'); // "Got: hello"`}},

'spl':{
desc:'SPL provides typed data structures: <code>SplStack</code>, <code>SplQueue</code>, <code>SplMinHeap</code>, <code>SplFixedArray</code>.',
s1:{label:'Stack, Queue, Heap',code:`<?php
$stack = new SplStack();
$stack->push('a'); $stack->push('b'); $stack->push('c');
echo $stack->pop(); // c (LIFO)

$queue = new SplQueue();
$queue->enqueue('first'); $queue->enqueue('second');
echo $queue->dequeue(); // first (FIFO)

$heap = new SplMinHeap();
foreach ([5, 1, 8, 3, 2] as $n) $heap->insert($n);
while (!$heap->isEmpty())
    echo $heap->extract() . ' '; // 1 2 3 5 8`},
s2:{label:'FixedArray & DoublyLinkedList',code:`<?php
$fa = new SplFixedArray(5);
for ($i = 0; $i < 5; $i++) $fa[$i] = $i * $i;
echo $fa[3]; // 9
echo $fa->getSize(); // 5

$dll = new SplDoublyLinkedList();
$dll->push('a'); $dll->push('b'); $dll->unshift('z');
echo $dll->bottom(); // z
echo $dll->top();    // b
echo $dll->count();  // 3

$arr = iterator_to_array($dll);`}},

'testing':{
desc:'PHPUnit is the standard testing framework. Write tests extending <code>TestCase</code> with data providers and mocks.',
s1:{label:'TestCase & assertions',code:`<?php
use PHPUnit\\Framework\\TestCase;

class CalculatorTest extends TestCase {
    private Calculator $calc;

    protected function setUp(): void {
        $this->calc = new Calculator();
    }

    public function testAdd(): void {
        $this->assertSame(5, $this->calc->add(2, 3));
    }

    public function testDivideByZeroThrows(): void {
        $this->expectException(DivisionByZeroError::class);
        $this->calc->divide(10, 0);
    }
}`},
s2:{label:'Data providers & mocks',code:`<?php
use PHPUnit\\Framework\\TestCase;
use PHPUnit\\Framework\\Attributes\\DataProvider;

class StringHelperTest extends TestCase {
    #[DataProvider('slugProvider')]
    public function testSlugify(string $in, string $out): void {
        $this->assertSame($out, StringHelper::slugify($in));
    }
    public static function slugProvider(): array {
        return [
            'basic'   => ['Hello World', 'hello-world'],
            'accents' => ['H\\u00e9llo', 'hello'],
        ];
    }
    public function testMock(): void {
        $repo = $this->createMock(UserRepository::class);
        $repo->method('find')->willReturn(new User(1, 'Alice'));
        $svc = new UserService($repo);
        $this->assertSame('Alice', $svc->getOrFail(1)->name);
    }
}`}}
};

// eslint-disable-next-line no-unused-vars
const QUIZZES={
'variables-types':[
{q:'What does <code>gettype(3.14)</code> return in PHP?',
opts:['"float"','"double"','"decimal"','"number"'],answer:1,
explanation:'PHP internally uses IEEE 754 double precision, so gettype() returns "double", not "float".'},
{q:'What is the output of <code>var_dump(true)</code>?',
opts:['true','1','bool(true)','boolean(1)'],answer:2,
explanation:'var_dump() shows the type and value: bool(true). echo true would output "1".'},
{q:'Which function verifies a variable exists AND is not null?',
opts:['empty()','isset()','is_null()','defined()'],answer:1,
explanation:'isset() returns true only if the variable exists and is not null.'},
{q:'What does <code>is_numeric("42.5")</code> return?',
opts:['false','true','null','42.5'],answer:1,
explanation:'is_numeric() returns true for strings that represent valid numbers, like "42.5" or "1e5".'}],

'type-casting':[
{q:'What is the result of <code>(int) "42px"</code>?',
opts:['0','42','"42"','Error'],answer:1,
explanation:'PHP parses the leading numeric portion: "42px" \u2192 42. If no numeric prefix (e.g. "px42"), result is 0.'},
{q:'What does <code>(bool) "0"</code> evaluate to?',
opts:['true','false','0','1'],answer:1,
explanation:'"0" is one of PHP\'s falsy values. Other falsy strings: "" (empty). But "false" is truthy!'},
{q:'What does <code>(bool) "false"</code> evaluate to?',
opts:['false','true','null','Error'],answer:1,
explanation:'"false" is a non-empty string that\'s not "0", so it evaluates to true. Only "" and "0" are falsy strings.'},
{q:'What is the result of <code>(array) "hello"</code>?',
opts:['["h","e","l","l","o"]','["hello"]','Error','[]'],answer:1,
explanation:'Casting a scalar to array wraps it in a single-element array: ["hello"]. Different from str_split().'}],

'math':[
{q:'What does <code>intdiv(7, 2)</code> return?',
opts:['3.5','4','3','3.0'],answer:2,
explanation:'intdiv() performs integer division, truncating towards zero. 7 \u00f7 2 = 3.5, truncated to 3.'},
{q:'What does <code>fdiv(1, 0)</code> return in PHP 8?',
opts:['Error','false','null','INF'],answer:3,
explanation:'fdiv() returns INF, -INF, or NAN instead of throwing errors. It\'s the "forgiving" division function.'},
{q:'What is <code>fmod(7.5, 2.0)</code>?',
opts:['3.5','1.5','1.0','0.5'],answer:1,
explanation:'fmod() is float modulo: 7.5 - 3\u00d72.0 = 1.5.'},
{q:'Which function generates a cryptographically secure random integer?',
opts:['rand()','mt_rand()','random_int()','lcg_value()'],answer:2,
explanation:'random_int() uses OS-level entropy and is suitable for security-sensitive code. rand() and mt_rand() are NOT cryptographically secure.'}],

'strings':[
{q:'Which function was added in PHP 8.0 to check if a string contains a substring?',
opts:['strstr()','strpos()','str_contains()','substr_count()'],answer:2,
explanation:'str_contains() was added in PHP 8.0 along with str_starts_with() and str_ends_with().'},
{q:'What does <code>strlen("h\u00e9llo")</code> return (UTF-8)?',
opts:['5','6','7','4'],answer:1,
explanation:'strlen() counts bytes, not characters. \u00e9 in UTF-8 is 2 bytes \u2192 6 total. Use mb_strlen() for characters.'},
{q:'What does <code>strpos("Hello World", "o")</code> return?',
opts:['3','4','7','true'],answer:1,
explanation:'strpos() returns the 0-based position of the first occurrence: H=0, e=1, l=2, l=3, o=4.'},
{q:'What is <code>str_pad("42", 5, "0", STR_PAD_LEFT)</code>?',
opts:['"42000"','"00042"','"0042"','"420"'],answer:1,
explanation:'STR_PAD_LEFT pads from the left. Result: "00042" (5 chars total).'}],

'output-buffering':[
{q:'What does <code>ob_get_clean()</code> do?',
opts:['Clears buffer without returning','Returns buffer contents and stops buffering','Flushes buffer to client','Returns buffer without stopping'],answer:1,
explanation:'ob_get_clean() = ob_get_contents() + ob_end_clean(). Returns contents and turns off buffering.'},
{q:'What does <code>sprintf("%.2f", 3.1)</code> return?',
opts:['"3.1"','"3.10"','"3.100"','3.10'],answer:1,
explanation:'%.2f formats with exactly 2 decimal places. sprintf() returns a string.'},
{q:'Key difference between printf() and sprintf()?',
opts:['printf() supports more formats','sprintf() returns the string; printf() writes it','printf() is faster','No difference'],answer:1,
explanation:'printf() outputs directly. sprintf() returns the formatted string without outputting it.'},
{q:'Which function flushes the buffer to the client?',
opts:['ob_clean()','ob_end_flush()','ob_flush()','flush()'],answer:2,
explanation:'ob_flush() sends the buffer to the web server. You usually also call flush() to push it to the client.'}],

'arrays':[
{q:'What does <code>array_map(fn($x) => $x*2, [1,2,3])</code> return?',
opts:['[2,4,6]','[1,2,3,2,4,6]','null','Error'],answer:0,
explanation:'array_map() applies the callback to each element, returning a new array. The original is unmodified.'},
{q:'What is <code>in_array("1", [1,2,3])</code> without strict mode?',
opts:['false','true','null','Error'],answer:1,
explanation:'Without strict=true, in_array() uses loose comparison. "1" == 1 is true. Use third arg true for strict.'},
{q:'Which function removes duplicates from an array?',
opts:['array_unique()','array_flip()','array_distinct()','array_diff()'],answer:0,
explanation:'array_unique() removes duplicate values. It preserves original keys.'},
{q:'What does <code>array_column($rows, "name", "id")</code> return?',
opts:['All "name" values','Array indexed by "id" with "name" values','Array of [id,name] pairs','The first "name"'],answer:1,
explanation:'With 3 args, array_column() uses the 3rd column as key and 2nd as value: [id => name, ...].'}],

'arrayobject':[
{q:'Which interfaces does ArrayObject implement?',
opts:['Iterator, Countable','ArrayAccess, Countable, IteratorAggregate','Traversable, Serializable','JsonSerializable, Countable'],answer:1,
explanation:'ArrayObject implements ArrayAccess ([] access), Countable (count()), and IteratorAggregate (foreach).'},
{q:'Which flag allows property-style access to ArrayObject elements?',
opts:['STD_PROP_LIST','ARRAY_AS_PROPS','OBJECT_AS_ARRAY','PROP_ACCESS'],answer:1,
explanation:'ARRAY_AS_PROPS enables $ao->key access instead of $ao["key"].'},
{q:'How do you convert an ArrayObject back to a plain array?',
opts:['(array) $ao','$ao->toArray()','$ao->getArrayCopy()','iterator_to_array($ao)'],answer:2,
explanation:'getArrayCopy() returns a copy of the internal array. (array) cast also works but getArrayCopy() is the OOP way.'},
{q:'What does <code>$ao->append($value)</code> do?',
opts:['Prepends to beginning','Appends with auto-numeric key','Replaces last element','Throws error'],answer:1,
explanation:'append() adds a value at the end with the next numeric key, similar to $arr[] = $value.'}],

'closures':[
{q:'What keyword defines an arrow function in PHP 7.4+?',
opts:['lambda','arrow','fn','=>'],answer:2,
explanation:'Arrow functions use fn: fn($x) => $x * 2. They auto-capture outer scope variables by value.'},
{q:'How does an arrow function capture outer variables?',
opts:['Explicitly with use','Automatically by value','Automatically by reference','They cannot capture'],answer:1,
explanation:'Arrow functions auto-capture by value (copy). Regular closures need explicit use ($var) or use (&$var) for reference.'},
{q:'What is the purpose of <code>Closure::bind()</code>?',
opts:['Converts function to closure','Binds closure to a different object/scope','Creates closure from method','Prevents double calling'],answer:1,
explanation:'Closure::bind($closure, $newThis, $newScope) creates a new closure bound to a specific object, accessing private members.'},
{q:'What does <code>(fn($x) => fn($y) => $x + $y)(3)(4)</code> return?',
opts:['Error','7','12','null'],answer:1,
explanation:'Currying: first call returns closure with $x=3 captured. Second passes $y=4. Result: 3 + 4 = 7.'}],

'datetime':[
{q:'Why prefer DateTimeImmutable over DateTime?',
opts:['It\'s faster','Mutations return new objects \u2014 original unchanged','Supports more time zones','Available since PHP 5.0'],answer:1,
explanation:'DateTimeImmutable methods return new objects instead of modifying in place, preventing subtle bugs.'},
{q:'What class represents a time interval (e.g., "3 months")?',
opts:['DateTimeInterval','TimeSpan','DateInterval','DatePeriod'],answer:2,
explanation:'DateInterval represents a duration: new DateInterval("P1Y2M3D") = 1 year, 2 months, 3 days.'},
{q:'What does <code>$dt->diff($other)->days</code> return?',
opts:['Signed difference','Absolute number of days between dates','Number of months','Difference in seconds'],answer:1,
explanation:'The days property always returns absolute days. Check the invert property for direction.'},
{q:'How do you parse "25/12/2025" into a DateTimeImmutable?',
opts:['new DateTimeImmutable("25/12/2025")','DateTimeImmutable::createFromFormat("d/m/Y","25/12/2025")','DateTimeImmutable::parse("d/m/Y","25/12/2025")','date_create("25/12/2025")'],answer:1,
explanation:'createFromFormat() takes a format string and the date string. Unrecognized formats return false.'}],

'json':[
{q:'What does the second argument <code>true</code> in json_decode do?',
opts:['Enables strict validation','Returns associative array instead of object','Allows JSON5 syntax','Throws on error'],answer:1,
explanation:'associative=true returns arrays instead of stdClass. PHP 8+: json_decode($j, associative: true).'},
{q:'Which flag makes json_decode/encode throw on error?',
opts:['JSON_STRICT','JSON_THROW_ON_ERROR','JSON_EXCEPTIONS','JSON_VALIDATE'],answer:1,
explanation:'JSON_THROW_ON_ERROR (PHP 7.3+) causes JsonException instead of returning null/false.'},
{q:'What does JSON_PRETTY_PRINT do?',
opts:['Validates against schema','Produces human-readable output with indentation','Sorts keys alphabetically','Strips whitespace'],answer:1,
explanation:'JSON_PRETTY_PRINT formats with newlines and 4-space indentation.'},
{q:'What does <code>json_encode(["x" => NAN])</code> return by default?',
opts:['{"x":null}','{"x":NaN}','false','Error'],answer:2,
explanation:'NAN cannot be represented in JSON. json_encode() returns false and sets JSON_ERROR_INF_OR_NAN.'}],

'regex':[
{q:'Which function returns ALL matches from a regex?',
opts:['preg_match()','preg_match_all()','preg_find_all()','preg_global()'],answer:1,
explanation:'preg_match() finds only the first match. preg_match_all() finds all occurrences.'},
{q:'How do you access a named capture group <code>(?P&lt;year&gt;\\d{4})</code>?',
opts:['$m->year','$m["year"]','$m[P<year>]','$m[1] only'],answer:1,
explanation:'Named captures are accessible as array keys: $m["year"]. Also available by numeric position.'},
{q:'What is <code>preg_replace(\'/\\s+/\', " ", "a  b  c")</code>?',
opts:['"a b c"','"abc"','"a  b  c"','Error'],answer:0,
explanation:'\\s+ matches one or more whitespace chars. Each run is replaced by a single space.'},
{q:'Besides /, which delimiters are valid for PHP regex?',
opts:['Only / is valid','Any non-alphanumeric, non-backslash char','Only brackets {}','Only # and @'],answer:1,
explanation:'PHP PCRE accepts any non-alphanumeric char as delimiter. Common: #pattern#, @pattern@, !pattern!.'}],

'generators':[
{q:'What is the return type of a generator function?',
opts:['iterable','array','Generator','yield'],answer:2,
explanation:'A function containing yield returns a Generator object. You can type-hint it as Generator or iterable.'},
{q:'What does <code>Generator::send($value)</code> do?',
opts:['Appends to future yields','It becomes the return value of the current yield expression','Replaces function return','It\'s ignored'],answer:1,
explanation:'send() resumes the generator AND passes $value as the return value of the yield expression inside.'},
{q:'Main advantage of generators over arrays?',
opts:['Faster iteration','Generate values lazily using constant memory','Type checking support','Can be sorted'],answer:1,
explanation:'Generators produce values one at a time, never holding the entire sequence in memory. O(1) vs O(n).'},
{q:'What happens calling next() on a returned generator?',
opts:['Restarts from beginning','Returns last value again','Generator becomes invalid \u2014 valid() returns false','Throws StopIteration'],answer:2,
explanation:'Once a generator returns, valid() returns false, current() returns null, next() does nothing.'}],

'mbstring':[
{q:'Why use mb_strlen() instead of strlen() for UTF-8?',
opts:['mb_strlen() is faster','strlen() counts bytes; mb_strlen() counts characters','strlen() doesn\'t support PHP 8','They are identical for ASCII'],answer:1,
explanation:'In UTF-8, chars like \u00e9, \u00f1 use multiple bytes. strlen("\u00e9") = 2, mb_strlen("\u00e9") = 1.'},
{q:'What does mb_detect_encoding() typically return for ASCII?',
opts:['"UTF-8"','"ASCII"','"ISO-8859-1"','false'],answer:0,
explanation:'Pure ASCII is valid UTF-8, so mb_detect_encoding() typically returns "UTF-8" first if it\'s in the detection list.'},
{q:'Which mb_ function was added in PHP 8.3?',
opts:['mb_pad()','mb_str_pad()','mb_str_fill()','mb_padding()'],answer:1,
explanation:'mb_str_pad() was added in PHP 8.3. Before that, str_pad() counted bytes, not characters.'},
{q:'What does <code>mb_str_split("h\u00e9llo")</code> return?',
opts:['Byte array','["h","\u00e9","l","l","o"]','["h\u00e9llo"]','Error'],answer:1,
explanation:'mb_str_split() (PHP 7.4+) splits into individual characters respecting Unicode codepoints.'}],

'oop':[
{q:'What does the <code>readonly</code> modifier do to a property?',
opts:['Makes it private','Allows writing only once (in constructor), prevents further modification','Makes it static','Prevents serialization'],answer:1,
explanation:'readonly (PHP 8.1+) allows a property to be written exactly once. Further assignment throws Error.'},
{q:'What is constructor promotion?',
opts:['Auto-calling parent::__construct()','Declaring property directly in constructor signature','Promoting constructor to factory','Making constructor public'],answer:1,
explanation:'Constructor promotion (PHP 8.0+): __construct(public string $name) {} declares and assigns in one step.'},
{q:'Key difference between interface and abstract class?',
opts:['Interfaces support type hints only','Interfaces have no implementations; abstract classes can','Abstract classes support multiple inheritance','No functional difference'],answer:1,
explanation:'Interfaces define a contract with no implementations. Abstract classes can mix abstract and concrete methods. A class can implement multiple interfaces.'},
{q:'What does <code>final</code> on a class do?',
opts:['Prevents instantiation','Prevents extending (inheriting)','Makes all methods static','Makes all properties readonly'],answer:1,
explanation:'A final class cannot be extended. A final method cannot be overridden.'}],

'namespaces':[
{q:'What is the namespace separator in PHP?',
opts:['.','/','::',' \\ (backslash)'],answer:3,
explanation:'PHP uses backslash \\\\ as separator: App\\\\Models\\\\User. Root is \\\\ClassName.'},
{q:'What does <code>use App\\Models\\User as U</code> do?',
opts:['Imports the file','Creates alias "U" for App\\Models\\User','Extends User','Makes User global'],answer:1,
explanation:'The "as" keyword creates an alias. Write U instead of App\\\\Models\\\\User in the current file.'},
{q:'PSR-4 maps App\\\\ to which directory?',
opts:['Project root','A configured source directory, commonly src/','The vendor/ folder','No standard'],answer:1,
explanation:'PSR-4 maps namespace prefixes to directories. App\\\\ \u2192 src/ means App\\\\Models\\\\User maps to src/Models/User.php.'},
{q:'Can you import a function with "use"?',
opts:['No, "use" is for classes only','Yes: use function App\\Helpers\\sanitize','Only built-in functions','Only with parentheses'],answer:1,
explanation:'PHP 5.6+ allows importing functions and constants with "use function" and "use const".'}],

'error-handling':[
{q:'What is the base interface for all throwable things in PHP 7+?',
opts:['Exception','Error','Throwable','BaseException'],answer:2,
explanation:'Throwable is implemented by both Exception and Error. catch (Throwable $e) catches everything.'},
{q:'When does the finally block run?',
opts:['Only if no exception','Only if exception','Always, regardless of exceptions','Only if caught'],answer:2,
explanation:'finally always runs \u2014 after try, after catch, even with return. Used for cleanup.'},
{q:'PHP 8.0 catch without variable \u2014 which is valid?',
opts:['catch (Exception $)','catch (Exception)','catch Exception','catch {}'],answer:1,
explanation:'PHP 8.0 allows: catch (Exception) {} when you don\'t need the exception object.'},
{q:'What function sets a handler for uncaught exceptions?',
opts:['register_exception_handler()','set_exception_handler()','on_exception()','catch_all()'],answer:1,
explanation:'set_exception_handler() registers a callback for uncaught exceptions.'}],

'filesystem':[
{q:'Which function reads an entire file into a string?',
opts:['fread()','file()','file_get_contents()','readfile()'],answer:2,
explanation:'file_get_contents() reads the whole file into a string. file() returns array of lines.'},
{q:'What does <code>file("/tmp/log.txt")</code> return?',
opts:['File handle','Boolean','Array of lines (including newlines)','File size'],answer:2,
explanation:'file() reads into an array where each element is a line. Use FILE_IGNORE_NEW_LINES to strip them.'},
{q:'What fopen mode appends and creates if not exists?',
opts:['"w"','"a"','"r+"','"x"'],answer:1,
explanation:'"a" opens for writing at end, creates if missing. "w" truncates. "r+" reads/writes. "x" creates only.'},
{q:'How to recursively create a directory tree?',
opts:['mkdir("/a/b/c")','mkdir("/a/b/c", 0755, recursive: true)','makedirs("/a/b/c")','fs_mkdir_recursive("/a/b/c")'],answer:1,
explanation:'mkdir() with recursive=true creates all intermediate directories.'}],

'http':[
{q:'Where must header() be called relative to output?',
opts:['After all HTML','Before any output is sent','Only inside a function','Anywhere \u2014 buffers auto'],answer:1,
explanation:'Headers must be sent before body. If any output (even whitespace) has been sent, header() fails.'},
{q:'How do you read the raw JSON request body?',
opts:['file_get_contents("php://stdin")','file_get_contents("php://input")','$_POST["body"]','$HTTP_RAW_POST_DATA'],answer:1,
explanation:'"php://input" gives raw request body. APIs use: json_decode(file_get_contents("php://input"), true).'},
{q:'Which superglobal holds uploaded files?',
opts:['$_POST','$_GET','$_FILES','$_REQUEST'],answer:2,
explanation:'$_FILES contains uploaded file info: name, type, tmp_name, error, size.'},
{q:'What does <code>http_response_code(404)</code> do?',
opts:['Throws 404 exception','Sets HTTP response status to 404','Redirects to 404 page','Returns "Not Found"'],answer:1,
explanation:'http_response_code() sets the HTTP status code. Without args, returns current code.'}],

'curl':[
{q:'What does <code>CURLOPT_RETURNTRANSFER => true</code> do?',
opts:['Compresses response','Returns response as string instead of outputting','Follows redirects','Enables SSL'],answer:1,
explanation:'By default, curl_exec() outputs directly. CURLOPT_RETURNTRANSFER makes it return a string.'},
{q:'How do you send a POST request with cURL?',
opts:['CURLOPT_METHOD => "POST"','CURLOPT_POST => true and CURLOPT_POSTFIELDS','CURLOPT_HTTP_POST => 1','curl_post($ch, $data)'],answer:1,
explanation:'Set CURLOPT_POST => true and CURLOPT_POSTFIELDS => $data. For JSON: also set Content-Type header.'},
{q:'What does <code>curl_getinfo($ch, CURLINFO_HTTP_CODE)</code> return?',
opts:['Content-Type header','HTTP status code (200, 404, etc.)','Response time in ms','Final URL after redirects'],answer:1,
explanation:'CURLINFO_HTTP_CODE gets the HTTP status. CURLINFO_TOTAL_TIME gets time. CURLINFO_EFFECTIVE_URL gets final URL.'},
{q:'What must you always call after a cURL session?',
opts:['curl_reset()','curl_destroy()','curl_close()','curl_free()'],answer:2,
explanation:'curl_close() releases the cURL handle and frees resources.'}],

'encryption':[
{q:'Best algorithm for password hashing in PHP 8?',
opts:['MD5','SHA-256','PASSWORD_BCRYPT','PASSWORD_ARGON2ID'],answer:3,
explanation:'PASSWORD_ARGON2ID (PHP 7.3+) is memory-hard and resistant to GPU/ASIC attacks. Bcrypt is acceptable but weaker.'},
{q:'Why use hash_equals() instead of === for comparing hashes?',
opts:['hash_equals() is faster','hash_equals() is constant-time (timing-attack safe)','=== only works for integers','hash_equals() normalizes format'],answer:1,
explanation:'=== can short-circuit on first differing byte, leaking timing info. hash_equals() always takes the same time.'},
{q:'What must be unique per message with AES-CBC?',
opts:['The key','The IV (Initialization Vector)','The algorithm','The key length'],answer:1,
explanation:'The IV must be unique and random per encryption. Reusing with same key breaks security. IV is not secret.'},
{q:'What does <code>random_bytes(32)</code> generate?',
opts:['32 random ASCII chars','32 cryptographically secure random bytes','A 32-digit number','A 32-char UUID'],answer:1,
explanation:'random_bytes() generates crypto-secure random data from OS entropy. Use for keys, tokens, IVs.'}],

'xml':[
{q:'When should you prefer DOMDocument over SimpleXML?',
opts:['For simple reading','For XPath queries','For complex modifications, namespaces, or building documents','DOMDocument is always better'],answer:2,
explanation:'SimpleXML is easy for reading. DOMDocument gives full W3C DOM control for complex transformations.'},
{q:'How do you read XML attribute "id" in SimpleXML?',
opts:['$node->id','$node->getAttribute("id")','(string) $node["id"]','$node->attr->id'],answer:2,
explanation:'SimpleXML elements support ArrayAccess: $node["id"]. Cast to (string) or (int) as needed.'},
{q:'How to run XPath in SimpleXML?',
opts:['$xml->query("xpath")','$xml->xpath("xpath")','$xml->select("xpath")','xpath($xml, "query")'],answer:1,
explanation:'$xml->xpath("//book") returns array of matching SimpleXMLElement nodes.'},
{q:'Which DOMDocument method creates a new element?',
opts:['$dom->addElement("tag")','$dom->newNode("tag")','$dom->createElement("tag")','$dom->createNode("tag")'],answer:2,
explanation:'createElement() creates a detached element node. Use appendChild() to attach it.'}],

'database-pdo':[
{q:'Why use prepared statements in PDO?',
opts:['They are faster','They prevent SQL injection by separating SQL from data','They support more DBs','They format dates'],answer:1,
explanation:'Prepared statements send SQL and data separately. The DB never interprets data as SQL.'},
{q:'What does PDO::FETCH_ASSOC return?',
opts:['An object','A numeric array','An associative array indexed by column name','Both numeric and string keys'],answer:2,
explanation:'FETCH_ASSOC returns ["column_name" => "value"]. FETCH_OBJ returns object. FETCH_BOTH returns both.'},
{q:'What does <code>$pdo->lastInsertId()</code> return?',
opts:['Last SELECT count','ID of the last inserted row','Affected rows count','Last query string'],answer:1,
explanation:'lastInsertId() returns the auto-generated ID from the last INSERT.'},
{q:'When should you use a database transaction?',
opts:['For all SELECTs','When multiple operations must all succeed or all fail','Only for large inserts','Never \u2014 DBs handle it'],answer:1,
explanation:'Transactions ensure atomicity. beginTransaction() + commit() + rollBack().'}],

'design-patterns':[
{q:'What does the Singleton pattern ensure?',
opts:['Creates objects from interface','Only one instance exists application-wide','Separates creation from usage','Notifies multiple objects'],answer:1,
explanation:'Singleton restricts to one instance with global access. Use sparingly \u2014 consider dependency injection.'},
{q:'The Strategy pattern primarily enables:',
opts:['Creating objects without concrete classes','Swapping algorithms/behaviors at runtime','One instance per class','Translating between interfaces'],answer:1,
explanation:'Strategy defines a family of algorithms, encapsulates each, and makes them interchangeable.'},
{q:'The Repository pattern abstracts:',
opts:['HTTP handling','The data persistence layer (database access)','Object creation','UI rendering'],answer:1,
explanation:'Repository provides a collection-like interface for domain objects ($repo->find(), $repo->save()).'},
{q:'<code>$config ??= loadDefaults()</code> is an example of:',
opts:['Singleton','Lazy initialization','Factory pattern','Chain of responsibility'],answer:1,
explanation:'??= initializes only if null/unset \u2014 lazy initialization. Value computed only when needed.'}],

'php8':[
{q:'Key difference between match() and switch()?',
opts:['match uses loose; switch uses strict','match uses strict comparison, no fallthrough, returns a value','match is for integers only','No functional difference'],answer:1,
explanation:'match() uses === (strict), no fallthrough, must be exhaustive, and is an expression that returns a value.'},
{q:'When does the nullsafe operator <code>?-></code> short-circuit?',
opts:['When right side throws','When left side is null','When method returns null','When property is private'],answer:1,
explanation:'$a?->b?->c \u2014 if $a is null, entire chain evaluates to null without calling b or c.'},
{q:'Enums were introduced in which PHP version?',
opts:['PHP 8.0','PHP 8.1','PHP 8.2','PHP 7.4'],answer:1,
explanation:'Enums are PHP 8.1. PHP 8.0 brought match, named args, union types, nullsafe. 8.1 added enums, readonly, fibers.'},
{q:'What are Fibers (PHP 8.1)?',
opts:['Array chunks','Stackful coroutines enabling cooperative multitasking','Typed collections','A way to extend enums'],answer:1,
explanation:'Fibers can be suspended (Fiber::suspend()) and resumed (->resume()), enabling async libraries.'}],

'spl':[
{q:'SplStack follows which principle?',
opts:['FIFO','LIFO (Last In, First Out)','Priority order','Random order'],answer:1,
explanation:'SplStack is LIFO \u2014 the last pushed item is the first popped. Like a stack of plates.'},
{q:'<code>SplMinHeap::extract()</code> returns:',
opts:['Random element','Largest element','Smallest element','Most recently inserted'],answer:2,
explanation:'SplMinHeap maintains the minimum at top. extract() removes and returns the minimum.'},
{q:'Advantage of SplFixedArray over regular array?',
opts:['Supports string keys','Less memory for large numeric arrays','Auto-sorted','Type checking'],answer:1,
explanation:'SplFixedArray stores fixed elements contiguously in C memory, using significantly less memory than hash-table arrays.'},
{q:'What does <code>SplQueue::dequeue()</code> return?',
opts:['Last element inserted','First element inserted (FIFO)','Smallest element','A copy of the queue'],answer:1,
explanation:'SplQueue is FIFO \u2014 dequeue() returns the element inserted first (front of queue).'}],

'testing':[
{q:'When does <code>setUp()</code> run in a PHPUnit test?',
opts:['Once before entire class','Before each individual test method','Only on first test','After all tests'],answer:1,
explanation:'setUp() runs before EACH test method, ensuring fresh state. tearDown() runs after each.'},
{q:'What is the purpose of a DataProvider?',
opts:['Loads data from DB','Supplies multiple data sets to run the same test repeatedly','Mocks external services','Generates random inputs'],answer:1,
explanation:'DataProvider lets one test run with multiple input sets. Provider returns array of arrays.'},
{q:'Which assertion checks both type and value (strict)?',
opts:['assertEquals()','assertSame()','assertStrictEquals()','assertIdentical()'],answer:1,
explanation:'assertSame() uses === (same type AND value). assertEquals() uses == (1 and "1" are equal).'},
{q:'What does <code>$this->createMock(Interface::class)</code> create?',
opts:['A real instance','A test double implementing the interface with all methods stubbed','A spy','A partial mock'],answer:1,
explanation:'createMock() generates a mock implementing the interface. Methods return null by default. Use willReturn() to configure.'}]
};
