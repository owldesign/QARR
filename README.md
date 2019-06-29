# Reviews & Ratings + Questions & Answers 

[Craft CMS Plugin](https://plugins.craftcms.com/qarr)

Grow your business by allowing visitors to leave a reviews or ask a questions.



## Ratings & Reviews

Collect reviews and ratings from customers and guests. Guest must have an account to leave a review. [See Documentation](https://docs.qarr.tools/reviews/)

* Moderate reviews for [Singles, Channels and Products](https://docs.qarr.tools/elements/reviews/#reviews-index)
* [Reply to feedback](https://docs.qarr.tools/elements/reviews/#review-entry)
* Follow up with an [email correspondence](https://docs.qarr.tools/elements/reviews/#email-correspondence)


## Questions & Answers

Collect questions from guest and allow customers to answer them. [See Documentation](https://docs.qarr.tools/questions/)

* Moderate questions for [Singles, Channels and Products](https://docs.qarr.tools/elements/questions/#reviews-index)
* Moderate [answers](https://docs.qarr.tools/elements/questions/#question-entry) 
* Follow up with an [email correspondence](https://docs.qarr.tools/elements/questions/#email-correspondence)


## Displays

If you want to capture additional information you can create fields and add them to [Displays](https://docs.qarr.tools/displays/)


## Whats Next?

[Join Slack Group](https://join.slack.com/t/qarrtools/shared_invite/enQtNDc0OTM0MTE2NjI1LTY4N2VhZDFjNmU4MjQ1ZThlZDJmMTcyNjM4MzhhZjhhMzIxOTNkMGU1Yjc5N2UwNDY0ZGNhOGYwMTc3Njg1MTU) - [Read Documentations](https://docs.qarr.tools) - [Visit Plugin Website](https://qarr.tools) - [Github Issues](https://github.com/owldesign/QARR/issues)

## Sample Usage

You can use this reviews plugin with any Singles, Channels or Commerce Product.

### Basic

`{{ craft.qarr.display(model) }}`

### Advanced
```
{{ craft.qarr.display(model, {
    limit: 3,
    pagination: 'infinite',
    reviews: {
        display: 'productReviews'
    },
    questions: false
}) }}
```	