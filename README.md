# Wwwision.Renderlets.Provider

Neos package to provide snippets of data/rendered content (aka 'renderlets') to be consumed by 3rd parties

## Installation

Install via composer:

    composer require wwwision/renderlets-provider

## Usage

Renderlets can be defined underneath the Fusion path `/renderlets`:

```neosfusion
renderlets {
    some_renderlet = Wwwision.Renderlets.Provider:Renderlet {
        renderer = afx`<p>This can be any component</p>`
    }
}
```

With this in place, the renderlet is exposed via HTTP on the endpoint `/__renderlets/some_renderlet`

### Caching

Each renderlet is assigned a `cacheId` that will be turned into an [ETag](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/ETag) HTTP header in the response.
This allows consumers to send a corresponding `If-None-Match` header in order to prevent unchanged renderlets from beeing transmitted again.

The `cacheId` is a random string by default that gets assigned at rendering time.
In order to keep that consistent, a corresponding `@cache` meta property is defined in the renderlet declaration (see https://docs.neos.io/guide/manual/rendering/caching).
If the renderlet content depends on other components or data, this property should be extended accordingly:

#### Example

```neosfusion
some_renderlet = Wwwision.Renderlets.Provider:Renderlet {
    @context {
        someNode = ${q(site).children('[instanceof Some.Package:SomeNodeType]').get(0)}
    }
    renderer = afx`Node label: {someNode.label}`
    @cache {
        entryTags {
            someNode = ${Neos.Caching.nodeTag(someNode)}
        }
    }
}
```

Alternatively, the `cacheId` can be set to a static (or dynamic) value to make it deterministic:

```neosfusion
some_renderlet = Wwwision.Renderlets.Provider:Renderlet {
    cacheId = 'some-static-value'
    renderer = afx`Static content`
}
```

### HTTP Headers

#### Content-Type

By default, renderlets are rendered with a `Content-Type` header of "text/html".
This can be changed via the `httpHeaders` prop:

```neosfusion
some_renderlet = Wwwision.Renderlets.Provider:Renderlet {
    httpHeaders {
        'Content-Type' = 'text/plain'
    }
    renderer = 'This is some plain text'
}
```

#### CORS

By default, renderlets are rendered with a `Access-Control-Allow-Origin` header of "*" to allow them to be consumed without [CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS) restrictions.
This can be changed via the `httpHeaders` prop:

```neosfusion
some_renderlet = Wwwision.Renderlets.Provider:Renderlet {
    httpHeaders {
        'Access-Control-Allow-Origin' = 'some-domain.tld'
    }
    // ...
}
```

#### Other headers

Other HTTP headers can be added via the `httpHeaders` prop:

```neosfusion
some_renderlet = Wwwision.Renderlets.Provider:Renderlet {
    httpHeaders {
        'Content-Language' = 'de-DE, en-CA'
        'X-Custom-Header' = 'some value'
    }
    // ...
}
```

### Renderlet Props

The `RenderletProps` prototype can be used to render data structures rather than (HTML) content:

```neosfusion
some_renderlet_props = Wwwision.Renderlets.Provider:RenderletProps {
    properties {
        foo = 'bar'
        baz {
           foos = true 
        }
    }
}
```

This will render the following JSON on the endpoint `/__renderlets/some_renderlet_props`:

```json
{
	"foo": "bar",
	"baz": {
		"foos": true
	}
}
```

The `Content-Type` header of `RenderletProps` is `application/json` by default, but it can be changed as described above.
