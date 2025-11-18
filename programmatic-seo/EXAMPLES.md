# Programmatic SEO - Template & Data Source Examples

## 📋 Template Examples

### 1. Product Listing Template
**Purpose**: Tạo hàng trăm trang sản phẩm từ dữ liệu

**Template Configuration:**
```
Template Name: Product Listing
Post Type: post
Slug: product-listing
```

**Title Template** (50-60 chars optimal):
```
{{product_name}} - {{category}} | {{store_name}}
```

**Meta Description Template** (120-160 chars):
```
Best {{product_name}} at {{store_name}}. {{short_description}} Price: {{price}}. Free shipping available.
```

**Keywords Template**:
```
{{product_name}}, {{category}}, {{brand}}, buy {{product_name}}, {{product_name}} price
```

**Content Template**:
```html
<h1>{{product_name}} - {{category}}</h1>

<div class="product-header">
  <p><strong>Price:</strong> {{price}}</p>
  <p><strong>Brand:</strong> {{brand}}</p>
  <p><strong>Rating:</strong> {{rating}}/5 ({{review_count}} reviews)</p>
</div>

<div class="product-description">
  <h2>Description</h2>
  <p>{{description}}</p>
</div>

<div class="product-specs">
  <h2>Key Features</h2>
  <ul>
    <li>{{feature_1}}</li>
    <li>{{feature_2}}</li>
    <li>{{feature_3}}</li>
  </ul>
</div>

<div class="product-availability">
  <h2>Availability</h2>
  <p>In Stock: {{in_stock}}</p>
  <p>Shipping Time: {{shipping_days}} days</p>
</div>

<div class="product-footer">
  <p>SKU: {{sku}}</p>
  <p>Category: {{category}}</p>
</div>
```

**Schema Template** (Product):
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "{{product_name}}",
  "description": "{{description}}",
  "sku": "{{sku}}",
  "brand": {
    "@type": "Brand",
    "name": "{{brand}}"
  },
  "offers": {
    "@type": "Offer",
    "url": "{{product_url}}",
    "priceCurrency": "{{currency}}",
    "price": "{{price}}",
    "availability": "https://schema.org/{{availability_schema}}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{rating}}",
    "reviewCount": "{{review_count}}"
  }
}
```

**Featured Image Field**: `product_image_url`

---

### 2. Blog Post Template
**Purpose**: Tạo hàng ngàn bài viết blog từ dữ liệu

**Template Configuration:**
```
Template Name: Blog Post
Post Type: post
Slug: blog-post
```

**Title Template**:
```
{{post_title}} - {{author_name}} Guide {{current_year}}
```

**Meta Description Template**:
```
Learn about {{post_title}} from {{author_name}}. {{excerpt}} Updated {{publish_date}}.
```

**Keywords Template**:
```
{{post_title}}, {{category}}, {{tag1}}, {{tag2}}, {{topic}}, tutorial, guide
```

**Content Template**:
```html
<div class="article-header">
  <p>By <strong>{{author_name}}</strong> | Published: {{publish_date}} | Category: {{category}}</p>
</div>

<div class="article-intro">
  <p>{{excerpt}}</p>
</div>

<div class="article-toc">
  <h2>Table of Contents</h2>
  <ul>
    <li><a href="#section-1">{{section_1_title}}</a></li>
    <li><a href="#section-2">{{section_2_title}}</a></li>
    <li><a href="#section-3">{{section_3_title}}</a></li>
  </ul>
</div>

<div class="article-content">
  <h2 id="section-1">{{section_1_title}}</h2>
  <p>{{section_1_content}}</p>

  <h2 id="section-2">{{section_2_title}}</h2>
  <p>{{section_2_content}}</p>

  <h2 id="section-3">{{section_3_title}}</h2>
  <p>{{section_3_content}}</p>
</div>

<div class="article-conclusion">
  <h2>Conclusion</h2>
  <p>{{conclusion}}</p>
</div>

<div class="article-meta">
  <p>Tags: {{tag1}}, {{tag2}}, {{tag3}}</p>
  <p>Author: <strong>{{author_name}}</strong></p>
</div>
```

**Schema Template** (BlogPosting):
```json
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{{post_title}}",
  "description": "{{excerpt}}",
  "articleBody": "{{article_body}}",
  "datePublished": "{{publish_date}}",
  "dateModified": "{{modified_date}}",
  "author": {
    "@type": "Person",
    "name": "{{author_name}}",
    "url": "{{author_url}}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{blog_name}}",
    "logo": "{{blog_logo}}"
  }
}
```

**Featured Image Field**: `featured_image`

---

### 3. Business/Location Template
**Purpose**: Tạo trang địa điểm kinh doanh

**Template Configuration:**
```
Template Name: Business Location
Post Type: page
Slug: business-location
```

**Title Template**:
```
{{business_name}} - {{city}}, {{state}} | {{service_type}}
```

**Meta Description Template**:
```
{{business_name}} in {{city}}, {{state}}. {{short_description}}. Call {{phone}}. Hours: {{hours}}
```

**Keywords Template**:
```
{{business_name}}, {{service_type}} {{city}}, {{city}} {{service_type}}, {{zipcode}}
```

**Content Template**:
```html
<div class="business-header">
  <h1>{{business_name}}</h1>
  <p class="tagline">{{tagline}}</p>
</div>

<div class="business-contact">
  <h2>Contact Information</h2>
  <ul>
    <li>📍 Address: {{street_address}}, {{city}}, {{state}} {{zipcode}}</li>
    <li>📞 Phone: <a href="tel:{{phone}}">{{phone}}</a></li>
    <li>📧 Email: <a href="mailto:{{email}}">{{email}}</a></li>
    <li>🌐 Website: <a href="{{website}}">{{website}}</a></li>
  </ul>
</div>

<div class="business-hours">
  <h2>Business Hours</h2>
  <table>
    <tr><td>Monday:</td><td>{{hours_monday}}</td></tr>
    <tr><td>Tuesday:</td><td>{{hours_tuesday}}</td></tr>
    <tr><td>Wednesday:</td><td>{{hours_wednesday}}</td></tr>
    <tr><td>Thursday:</td><td>{{hours_thursday}}</td></tr>
    <tr><td>Friday:</td><td>{{hours_friday}}</td></tr>
    <tr><td>Saturday:</td><td>{{hours_saturday}}</td></tr>
    <tr><td>Sunday:</td><td>{{hours_sunday}}</td></tr>
  </table>
</div>

<div class="business-description">
  <h2>About Us</h2>
  <p>{{description}}</p>
</div>

<div class="business-services">
  <h2>Services</h2>
  <ul>
    <li>{{service_1}}</li>
    <li>{{service_2}}</li>
    <li>{{service_3}}</li>
  </ul>
</div>

<div class="business-rating">
  <h2>Customer Reviews</h2>
  <p>Rating: {{rating}}/5 ({{review_count}} reviews)</p>
  <p>"{{customer_review}}"</p>
</div>
```

**Schema Template** (LocalBusiness):
```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "{{business_name}}",
  "description": "{{description}}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{street_address}}",
    "addressLocality": "{{city}}",
    "addressRegion": "{{state}}",
    "postalCode": "{{zipcode}}",
    "addressCountry": "{{country}}"
  },
  "telephone": "{{phone}}",
  "email": "{{email}}",
  "url": "{{website}}",
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": "Monday",
    "opens": "{{opening_time}}",
    "closes": "{{closing_time}}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{rating}}",
    "reviewCount": "{{review_count}}"
  }
}
```

**Featured Image Field**: `business_image`

---

### 4. Service/Package Template
**Purpose**: Tạo trang dịch vụ hoặc gói dịch

**Template Configuration:**
```
Template Name: Service Package
Post Type: page
Slug: service-package
```

**Title Template**:
```
{{service_name}} - {{service_type}} | Starts at {{base_price}}
```

**Meta Description Template**:
```
{{service_name}} service. {{service_description}} From {{base_price}}. Trusted by {{client_count}}+ clients.
```

**Keywords Template**:
```
{{service_name}}, {{service_type}}, {{service_category}}, professional {{service_type}}
```

**Content Template**:
```html
<div class="service-hero">
  <h1>{{service_name}}</h1>
  <p class="intro">{{service_description}}</p>
  <p class="price">Starting at <strong>{{base_price}}</strong></p>
</div>

<div class="service-benefits">
  <h2>Why Choose {{service_name}}?</h2>
  <ul>
    <li>{{benefit_1}}</li>
    <li>{{benefit_2}}</li>
    <li>{{benefit_3}}</li>
    <li>{{benefit_4}}</li>
  </ul>
</div>

<div class="service-details">
  <h2>What's Included</h2>
  <p>{{detailed_description}}</p>
</div>

<div class="service-pricing">
  <h2>Pricing Options</h2>
  <div class="pricing-table">
    <div class="price-option">
      <h3>{{package_1_name}}</h3>
      <p class="price">{{package_1_price}}</p>
      <ul>
        <li>{{package_1_feature_1}}</li>
        <li>{{package_1_feature_2}}</li>
      </ul>
    </div>
    <div class="price-option featured">
      <h3>{{package_2_name}}</h3>
      <p class="price">{{package_2_price}}</p>
      <ul>
        <li>{{package_2_feature_1}}</li>
        <li>{{package_2_feature_2}}</li>
        <li>{{package_2_feature_3}}</li>
      </ul>
    </div>
  </div>
</div>

<div class="service-testimonials">
  <h2>What Our Clients Say</h2>
  <blockquote>"{{testimonial}}" - {{testimonial_author}}</blockquote>
</div>

<div class="service-cta">
  <h2>Ready to Get Started?</h2>
  <p>{{cta_text}}</p>
  <p><a href="{{booking_url}}" class="btn">Book Now</a></p>
</div>
```

**Schema Template** (Service):
```json
{
  "@context": "https://schema.org",
  "@type": "Service",
  "name": "{{service_name}}",
  "description": "{{service_description}}",
  "serviceType": "{{service_type}}",
  "provider": {
    "@type": "Organization",
    "name": "{{company_name}}"
  },
  "offers": {
    "@type": "Offer",
    "priceCurrency": "{{currency}}",
    "price": "{{base_price}}"
  },
  "areaServed": {
    "@type": "Place",
    "name": "{{service_area}}"
  },
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{rating}}",
    "reviewCount": "{{review_count}}"
  }
}
```

**Featured Image Field**: `service_image`

---

## 📊 Data Source Examples

### 1. Product CSV Data Source
**Type**: CSV
**File Location**: `/var/www/products.csv`

**CSV Content** (First row = headers):
```csv
product_name,category,brand,price,rating,review_count,description,short_description,sku,product_image_url,in_stock,shipping_days,store_name,currency,availability_schema
"iPhone 15 Pro","Electronics","Apple","$999","4.8","2500","The latest flagship smartphone with advanced camera and processing power.","Premium smartphone with AI features","IPHONE15PRO","https://example.com/iphone15.jpg","true","2","Tech Store","USD","InStock"
"Samsung Galaxy S24","Electronics","Samsung","$899","4.7","1800","Powerful Android smartphone with excellent display.","Premium Android phone","SAMSUNG_S24","https://example.com/galaxy.jpg","true","2","Tech Store","USD","InStock"
"MacBook Pro 16","Electronics","Apple","$2499","4.9","500","Professional laptop for developers and creators.","Powerful laptop for professionals","MBP16_M3","https://example.com/macbook.jpg","true","3","Tech Store","USD","InStock"
"iPad Air","Electronics","Apple","$599","4.6","1200","Versatile tablet for work and entertainment.","Mid-range tablet","IPADAIR2024","https://example.com/ipad.jpg","true","2","Tech Store","USD","InStock"
```

**Field Mapping** (in Data Source):
```json
{
  "product_name": "product_name",
  "category": "category",
  "brand": "brand",
  "price": "price",
  "rating": "rating",
  "review_count": "review_count",
  "description": "description",
  "short_description": "short_description",
  "sku": "sku",
  "product_image_url": "product_image_url",
  "in_stock": "in_stock",
  "shipping_days": "shipping_days",
  "store_name": "store_name",
  "currency": "currency",
  "availability_schema": "availability_schema"
}
```

---

### 2. Blog Posts JSON Data Source
**Type**: JSON
**Content Type**: Inline JSON

**JSON Content**:
```json
[
  {
    "post_title": "10 Tips for Better Photography",
    "author_name": "John Doe",
    "category": "Photography",
    "publish_date": "2024-01-15",
    "modified_date": "2024-01-20",
    "excerpt": "Learn the fundamental photography tips that will transform your images.",
    "section_1_title": "Understand Lighting",
    "section_1_content": "Lighting is the foundation of great photography. Master natural light, golden hour, and artificial lighting techniques.",
    "section_2_title": "Composition Basics",
    "section_2_content": "Learn about rule of thirds, leading lines, and framing to create compelling compositions.",
    "section_3_title": "Camera Settings",
    "section_3_content": "Understand ISO, aperture, and shutter speed to take control of your camera.",
    "conclusion": "Practice these tips regularly and your photography skills will improve dramatically.",
    "tag1": "photography",
    "tag2": "tips",
    "tag3": "tutorial",
    "topic": "Photography",
    "featured_image": "https://example.com/photography-tips.jpg",
    "current_year": "2024",
    "blog_name": "Photography Hub",
    "blog_logo": "https://example.com/logo.png",
    "author_url": "https://example.com/author/john-doe",
    "article_body": "Full article content here..."
  },
  {
    "post_title": "Mastering SEO in 2024",
    "author_name": "Jane Smith",
    "category": "SEO",
    "publish_date": "2024-01-10",
    "modified_date": "2024-01-18",
    "excerpt": "Complete guide to SEO optimization strategies for 2024.",
    "section_1_title": "Keyword Research",
    "section_1_content": "Find the right keywords that your audience is searching for.",
    "section_2_title": "On-Page SEO",
    "section_2_content": "Optimize your content, titles, and meta descriptions.",
    "section_3_title": "Link Building",
    "section_3_content": "Create quality backlinks to improve domain authority.",
    "conclusion": "SEO is an ongoing process that requires consistency and strategy.",
    "tag1": "seo",
    "tag2": "marketing",
    "tag3": "guide",
    "topic": "SEO",
    "featured_image": "https://example.com/seo-guide.jpg",
    "current_year": "2024",
    "blog_name": "Marketing Hub",
    "blog_logo": "https://example.com/logo.png",
    "author_url": "https://example.com/author/jane-smith",
    "article_body": "Full article content here..."
  }
]
```

---

### 3. Business Locations CSV Data Source
**Type**: CSV

**CSV Content**:
```csv
business_name,city,state,zipcode,street_address,phone,email,website,tagline,description,service_type,hours_monday,hours_tuesday,hours_wednesday,hours_thursday,hours_friday,hours_saturday,hours_sunday,rating,review_count,customer_review,service_1,service_2,service_3,business_image,country,opening_time,closing_time
"John's Auto Repair","New York","NY","10001","123 Main St","(212) 555-0100","info@johnsautorepair.com","https://johnsautorepair.com","Professional Auto Repair Since 1995","We provide comprehensive auto repair services for all makes and models.","Auto Repair","9am-6pm","9am-6pm","9am-6pm","9am-6pm","9am-6pm","10am-4pm","Closed","4.8","520","Best mechanic in town!","Engine Repair","Oil Changes","Brake Service","https://example.com/johns-auto.jpg","USA","09:00","18:00"
"Green Yoga Studio","Los Angeles","CA","90001","456 Oak Ave","(213) 555-0200","hello@greenyoga.com","https://greenyoga.com","Transform Your Body and Mind","Experience professional yoga instruction in a peaceful environment.","Yoga Studio","6am-8pm","6am-8pm","6am-8pm","6am-8pm","6am-8pm","8am-6pm","10am-4pm","4.9","380","Amazing classes and instructors!","Yoga Classes","Pilates","Personal Training","https://example.com/yoga-studio.jpg","USA","06:00","20:00"
"Pizza Paradise","Chicago","IL","60601","789 Elm St","(312) 555-0300","order@pizzaparadise.com","https://pizzaparadise.com","Authentic Italian Pizza","Wood-fired pizzas made with traditional Italian recipes.","Pizza Restaurant","11am-10pm","11am-10pm","11am-10pm","11am-10pm","11am-11pm","12pm-11pm","12pm-10pm","4.7","650","Best pizza in Chicago!","Pizza","Pasta","Desserts","https://example.com/pizza.jpg","USA","11:00","22:00"
```

---

### 4. Services API Data Source
**Type**: API
**Endpoint**: `https://api.example.com/services`
**Method**: GET

**API Response Example**:
```json
{
  "data": [
    {
      "service_name": "Web Development",
      "service_type": "Digital Services",
      "service_category": "Web Design & Development",
      "service_description": "Custom website development services tailored to your business needs.",
      "detailed_description": "We create responsive, fast-loading websites that convert visitors into customers.",
      "base_price": "$2,000",
      "company_name": "Tech Solutions Inc",
      "benefit_1": "Custom Design",
      "benefit_2": "Mobile Responsive",
      "benefit_3": "SEO Optimized",
      "benefit_4": "Fast Performance",
      "package_1_name": "Starter",
      "package_1_price": "$2,000",
      "package_1_feature_1": "5 Pages",
      "package_1_feature_2": "Basic SEO",
      "package_2_name": "Professional",
      "package_2_price": "$5,000",
      "package_2_feature_1": "Unlimited Pages",
      "package_2_feature_2": "Advanced SEO",
      "package_2_feature_3": "CMS Integration",
      "testimonial": "They delivered exactly what we needed on time and on budget!",
      "testimonial_author": "Sarah Johnson, CEO",
      "rating": "4.9",
      "review_count": "450",
      "service_area": "United States",
      "currency": "USD",
      "booking_url": "https://tech-solutions.com/booking",
      "cta_text": "Let's build your perfect website!",
      "service_image": "https://example.com/web-dev.jpg"
    },
    {
      "service_name": "Digital Marketing",
      "service_type": "Marketing Services",
      "service_category": "Online Marketing",
      "service_description": "Comprehensive digital marketing strategies to grow your business online.",
      "detailed_description": "From SEO to social media, we handle all aspects of digital marketing.",
      "base_price": "$1,500/month",
      "company_name": "Marketing Pro",
      "benefit_1": "Increased Visibility",
      "benefit_2": "More Leads",
      "benefit_3": "Better ROI",
      "benefit_4": "24/7 Monitoring",
      "package_1_name": "Starter",
      "package_1_price": "$1,500/month",
      "package_1_feature_1": "SEO Optimization",
      "package_1_feature_2": "Monthly Reports",
      "package_2_name": "Premium",
      "package_2_price": "$3,500/month",
      "package_2_feature_1": "Full SEO + SEM",
      "package_2_feature_2": "Social Media Management",
      "package_2_feature_3": "Content Creation",
      "testimonial": "Our online sales increased by 150% in just 6 months!",
      "testimonial_author": "Mike Chen, Founder",
      "rating": "4.8",
      "review_count": "380",
      "service_area": "North America",
      "currency": "USD",
      "booking_url": "https://marketing-pro.com/booking",
      "cta_text": "Grow your business with digital marketing!",
      "service_image": "https://example.com/digital-marketing.jpg"
    }
  ]
}
```

**API Headers** (if authentication needed):
```json
{
  "Authorization": "Bearer YOUR_API_TOKEN",
  "Content-Type": "application/json"
}
```

**API Parameters**:
```json
{
  "limit": "100",
  "offset": "0"
}
```

---

## 🚀 Getting Started

### Step-by-Step Setup

1. **Create Template**
   - Go to Programmatic SEO > Templates
   - Click "Add New Template"
   - Copy-paste template content from above
   - Set featured image field (e.g., `product_image_url`)
   - Save template

2. **Create Data Source**
   - Go to Programmatic SEO > Data Sources
   - Click "Add New Data Source"
   - Choose type (CSV, JSON, or API)
   - Add your data (CSV path, JSON data, or API endpoint)
   - Set up field mapping if needed
   - Test the data source
   - Save

3. **Generate Pages**
   - Go to Programmatic SEO > Page Generator
   - Select your template
   - Select your data source
   - Click "Generate Pages"
   - Monitor progress

4. **Verify**
   - Check Programmatic SEO > Analytics
   - Verify pages in WordPress Posts
   - Test SEO with Google Tools

---

## 💡 Tips & Best Practices

1. **Template Variables**
   - Use consistent naming: `{{variable_name}}`
   - Keep field names lowercase with underscores
   - Match variable names to CSV headers/JSON keys

2. **Data Sources**
   - CSV: First row must be headers
   - JSON: Must be array of objects
   - API: Must return JSON with array or data property

3. **Content Quality**
   - Add meaningful descriptions
   - Include rich content sections
   - Use proper HTML structure
   - Optimize for readability

4. **SEO Optimization**
   - Use target keywords in title & description
   - Vary templates for different content types
   - Include schema markup
   - Add internal linking hints

5. **Testing**
   - Test with small data sets first
   - Preview templates before generating
   - Check generated pages for quality
   - Validate schema markup

---

## ✅ Validation Checklist

Before generating pages:

- [ ] Template name is descriptive
- [ ] All variables are properly formatted: `{{variable}}`
- [ ] Variable names match data source fields
- [ ] Title is 50-60 characters
- [ ] Meta description is 120-160 characters
- [ ] Content includes multiple sections
- [ ] Schema template is valid JSON
- [ ] Featured image field is specified
- [ ] Data source is tested and working
- [ ] Field mapping is configured (if needed)
- [ ] Sample data preview looks correct
