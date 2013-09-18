
<!-- saved from url=(0072)https://raw.github.com/yihui/knitr/master/inst/examples/knitr-minimal.md -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style type="text/css"></style></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;"># A Minimal Example for Markdown

This is a minimal example of using **knitr** to produce an _HTML_ page from _Markdown_.

## R code chunks


```r
# set global chunk options: images will be 7x5 inches
opts_chunk$set(fig.width = 7, fig.height = 5)
```


Now we write some code chunks in this markdown file:


```r
x &lt;- 1 + 1  # a simple calculator
set.seed(123)
rnorm(5)  # boring random numbers
```

```
## [1] -0.56048 -0.23018  1.55871  0.07051  0.12929
```


We can also produce plots:


```r
par(mar = c(4, 4, 0.1, 0.1))
with(mtcars, {
    plot(mpg ~ hp, pch = 20, col = "darkgray")
    lines(lowess(hp, mpg))
})
```

![plot of chunk graphics](http://animation.r-forge.r-project.org/ideas/figure/graphics.png) 


## Inline code

Inline R code is also supported, e.g. the value of `x` is 2, and 2 &amp;times; &amp;pi;
= 6.2832.

## Math

LaTeX math as usual: $f(\alpha, \beta) \propto x^{\alpha-1}(1-x)^{\beta-1}$.

## Misc

You can indent code chunks so they can nest within other environments such as lists.

1. the area of a circle with radius x
    
    ```r
    pi * x^2
    ```
    
    ```
    ## [1] 12.57
    ```

2. OK, that is great

To compile me, use


```r
library(knitr)
knit("knitr-minimal.Rmd")
```


## Conclusion

Markdown is super easy to write. Go to **knitr** [homepage](http://yihui.name/knitr) for details.
</pre></body></html>