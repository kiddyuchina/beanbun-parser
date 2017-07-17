beanbun-parser 

### 简介
beanbun-parser 是 Beanbun 的数据抽取插件。通过设置抽取规则，在每次爬取页面之后，可以自动提取页面数据到数组中以供使用。抽取规则的选择器语法类似于 jQuery，使用简单。  
插件使用了 [phpQuery]() 和 [querylist]() 两个包。 

### 使用
只需实例化后，通过 Beanbun::middleware() 加载即可。实例化时，可接受一个参数，类型为数组，内容为对 beanbun-parser 的配置，加载后 Beanbun 实例会增加 $parser 属性，属性值即为 beanbun-parser 实例。  
目前只接受一个选项 auto，即插件是否按照规则自动抽取数据，默认为 true。  
auto 为 true 时，Beanbun 实例会增加 $fields、$data 两个属性。$fields 为抽取规则，$data 为抽取到的数据。  

```php
<?php
use Beanbun\Beanbun;
use Beanbun\Middleware\Parser;

$beanbun = new Beanbun;
$beanbun->name = '950d';
$beanbun->seed = 'http://www.950d.com/';

$parser = new Parser;
$beanbun->middleware($parser);
```

### Beanbun 属性  
#### Beanbun::$fields  
$fields 每个抽取项可以包含一下元素  
name: 此项数据的变量名  
selector: 抽取规则。包含两个元素，前一个为 jQuery 风格的选择器，后一个为要抽取的属性，可选为 text、html、[HTML标签属性]:如src、href、name、data-src等任意HTML标签属性名  
repeated: 定义抽取到的内容是否是有多项, 默认 false  
required: 定义该 field 的值是否必须, 默认 false  
children: 为此 field 定义子项，子项的定义仍然是一个fields数组，没错, 这是一个树形结构  
```php
$beanbun->fields = [
    [
        'name' => 'title',
        'selector' => ['title', 'text']
    ],
    [
        'name' => 'template',
        'children' => [
            [
                'name' => 'title',
                'selector' => ['.js-course-list li h5', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'url',
                'selector' => ['.js-course-list li .course-list-img a', 'href'],
                'repeated' => true,
            ],
            [
                'name' => 'image',
                'selector' => ['.js-course-list li .course-list-img img', 'src'],
                'repeated' => true,
            ]
        ]
    ]
];
```

#### Beanbun::$data 
$data 是抽取到的数据，在 Beanbun 中 afterDownloadPage 和其之后的回调函数中都可以使用 
```php
$beanbun->afterDownloadPage = function($beanbun) {
    print_r($beanbun->data);
};

// 上面例子中抽取到的数据为
$beanbun->data = [
    'title' => '企业网站模板 - Finecms模板 Duxcms模板 Doccms模板 稻壳cms模板',
    'template' => [
        'title' => [
            '旅游类通用型手机站模板',
            '简洁高效多产品分类模板',
            '虚拟商品销售网站Doccms模板',
            '幼儿园幼儿教育Doccms网站模板',
            '宠物会馆职业培训类Doccms模板',
            '蓝色物流运输类Doccms模板',
            '设计公司Duxcms手机网站模板',
            '设计公司Duxcms网站模板',
            'Doccms2016版大气简洁企业站模板',
            '响应式红色企业网站模板',
            '投资金融贷款类企业网站模板',
            '投资贷款类企业手机模板'
        ],
        'url' => [
            'http://www.950d.com/list/187.html',
            'http://www.950d.com/list/184.html',
            'http://www.950d.com/list/183.html',
            'http://www.950d.com/list/182.html',
            'http://www.950d.com/list/181.html',
            'http://www.950d.com/list/180.html',
            'http://www.950d.com/list/179.html',
            'http://www.950d.com/list/178.html',
            'http://www.950d.com/list/177.html',
            'http://www.950d.com/list/176.html',
            'http://www.950d.com/list/175.html',
            'http://www.950d.com/list/174.html'
        ],
        'image' => [
            '/upload/2016-12-27/2c41a2b55cc1123a2909487e9c078969.jpg',
            '/upload/2016-11-05/41bac823202e3f8b37dccb285f09b7ca.jpg',
            '/upload/2016-11-05/336269e55db23da60e519d4806f6d2b0.jpg',
            '/upload/2016-11-05/913ed6669b8cf2de0d366c55f0917002.jpg',
            '/upload/2016-11-05/1760bd081855d178e48bd420a42d34d4.jpg',
            '/upload/2016-11-05/614212d8bd4b4b7d2072300edb0e101d.jpg',
            '/upload/2016-11-04/b5a2eae483169a602d6742ab383c772d.jpg',
            '/upload/2016-11-04/62b40db4bd2ee13a0bcf4e49eae166aa.jpg',
            '/upload/2016-03-22/21d397aa278643d7489533827d16bfa2.jpg',
            '/upload/2016-10-12/d09c689ce01a525b631a5b2b56e052bc.jpg',
            '/upload/2016-09-22/c2ad9f776f424309b89ff24bdefd152b.jpg',
            '/upload/2016-09-22/d4b32be547ad65a9fd84a14e45e60180.jpg'
        ]
    ]
];
```

### Beanbun::$parser 可用方法  
getData  
接受一个参数 $feilds，格式与上面提到的 Beanbun::$fields 相同。 
```php
$beanbun->afterDownloadPage = function($beanbun) {
    $data = $beanbun->parser->getData([
        [
            'name' => 'title',
            'selector' => ['title', 'text']
        ]
    ]);
    print_r($data);
};

```


### 完整示例
``` php
use Beanbun\Beanbun;
use Beanbun\Middleware\Parser;

require_once(__DIR__ . '/vendor/autoload.php');

$beanbun = new Beanbun;
$beanbun->name = '950d';
$beanbun->count = 5;
$beanbun->seed = 'http://www.950d.com/';
$beanbun->max = 100;
$beanbun->urlRegex = [
    '/http:\/\/www.950d.com\/list-1.html\?page=(\d*)/'
];

$beanbun->middleware(new Parser());
$beanbun->fields = [
    [
        'name' => 'title',
        'selector' => ['title', 'text']
    ],
    [
        'name' => 'template',
        'children' => [
            [
                'name' => 'title',
                'selector' => ['.js-course-list li h5', 'text'],
                'repeated' => true,
            ],
            [
                'name' => 'url',
                'selector' => ['.js-course-list li .course-list-img a', 'href'],
                'repeated' => true,
            ],
            [
                'name' => 'image',
                'selector' => ['.js-course-list li .course-list-img img', 'src'],
                'repeated' => true,
            ]
        ]
    ]
];

$beanbun->afterDownloadPage = function($beanbun) {
    print_r($beanbun->data);
};
$beanbun->start();
```

