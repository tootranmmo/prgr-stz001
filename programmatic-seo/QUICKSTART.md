# Programmatic SEO - Quick Start Guide

## ⚡ 5 Phút Để Bắt Đầu

### 1️⃣ Cài Đặt Plugin

```bash
1. Upload thư mục programmatic-seo vào /wp-content/plugins/
2. Vào WordPress Admin > Plugins
3. Tìm "Programmatic SEO" và click Activate
4. Database tables tự động tạo
```

---

### 2️⃣ Tạo Template Đầu Tiên

**Vào**: Programmatic SEO > Templates > Add New Template

```
Template Name: My First Template
Post Type: post
```

**Title Template**:
```
{{title}} | Learn More
```

**Meta Description**:
```
Discover {{title}}. {{description}}. Read our comprehensive guide.
```

**Content Template**:
```html
<h1>{{title}}</h1>
<p>{{description}}</p>
<h2>Key Points</h2>
<ul>
  <li>{{point1}}</li>
  <li>{{point2}}</li>
  <li>{{point3}}</li>
</ul>
```

**Save Template** ✅

---

### 3️⃣ Tạo Data Source

**Vào**: Programmatic SEO > Data Sources > Add New

#### Option A: JSON Data (Dễ nhất)

```
Name: Sample Data
Type: JSON
```

**JSON Data**:
```json
[
  {
    "title": "Complete Guide to SEO",
    "description": "Master SEO from scratch with this comprehensive guide",
    "point1": "Keyword research strategy",
    "point2": "On-page optimization",
    "point3": "Link building techniques"
  },
  {
    "title": "WordPress Performance Tips",
    "description": "Speed up your WordPress site with these proven tips",
    "point1": "Enable caching",
    "point2": "Optimize images",
    "point3": "Minimize CSS/JS"
  },
  {
    "title": "Email Marketing Best Practices",
    "description": "Learn how to build an effective email marketing strategy",
    "point1": "Build quality list",
    "point2": "Create compelling subject lines",
    "point3": "Personalize content"
  }
]
```

**Save Data Source** ✅

---

### 4️⃣ Generate Pages

**Vào**: Programmatic SEO > Page Generator

```
Select Template: My First Template
Select Data Source: Sample Data
Click: Generate Pages
```

**Result**: 3 trang sẽ được tạo tự động! 🎉

---

### 5️⃣ Xem Kết Quả

1. **Vào**: WordPress > Posts
2. **Bạn sẽ thấy**: 3 bài viết mới được tạo
3. **Click vào bài viết**: Xem nội dung được tạo từ template

---

## 📊 Ví Dụ Hoàn Chỉnh

### Tạo 100 Trang Sản Phẩm

**Template** (Programmatic SEO > Templates > Add New):

```
Name: Product Page
```

**Title Template**:
```
{{product_name}} - Best {{category}} Online | {{discount}}% Off
```

**Content Template**:
```html
<h1>{{product_name}}</h1>
<p class="price">💰 {{price}}</p>
<p>{{description}}</p>

<h2>Features</h2>
<ul>
  <li>{{feature1}}</li>
  <li>{{feature2}}</li>
  <li>{{feature3}}</li>
</ul>

<h2>Why Choose This?</h2>
<p>{{benefits}}</p>

<h2>Customer Reviews</h2>
<p>⭐ {{rating}}/5 ({{reviews}} reviews)</p>
<blockquote>{{review_text}}</blockquote>
```

**Data Source** (Programmatic SEO > Data Sources > Add New):

```json
[
  {
    "product_name": "Wireless Headphones Pro",
    "category": "Audio",
    "price": "$149.99",
    "discount": "25",
    "description": "High-quality wireless headphones with active noise cancellation",
    "feature1": "40-hour battery life",
    "feature2": "Active noise cancellation",
    "feature3": "Premium sound quality",
    "benefits": "Perfect for professionals and music lovers",
    "rating": "4.8",
    "reviews": "2500",
    "review_text": "Best headphones I've ever owned!"
  },
  {
    "product_name": "Smartphone Stand",
    "category": "Accessories",
    "price": "$29.99",
    "discount": "40",
    "description": "Adjustable smartphone stand for desk and travel",
    "feature1": "360-degree rotation",
    "feature2": "Fits all phones",
    "feature3": "Portable design",
    "benefits": "Great for video calls and content creation",
    "rating": "4.6",
    "reviews": "1200",
    "review_text": "Sturdy and works perfectly!"
  }
]
```

**Generate**: 2 trang sản phẩm ✅

---

## 🎯 Use Cases - Mẫu Sẵn Sàng

### 1. Blog Posts từ Outline
**Tạo** 1000+ bài viết blog từ các outline đơn giản

### 2. Product Listings
**Tạo** 500+ trang sản phẩm từ CSV hoặc API

### 3. Location Pages
**Tạo** 100+ trang địa điểm kinh doanh

### 4. Service Pages
**Tạo** 50+ trang dịch vụ khác nhau

### 5. FAQ Pages
**Tạo** 200+ trang FAQ từ câu hỏi và câu trả lời

---

## 🔧 Template Variables - Cheat Sheet

```
{{variable_name}}  → Thay thế bằng giá trị từ data

Ví dụ:
  {{title}}         → "My Product"
  {{price}}         → "$99.99"
  {{description}}   → "High-quality product..."
  {{rating}}        → "4.8"
```

---

## 📱 Admin Dashboard Features

### Templates
- ✅ Create / Edit / Delete
- ✅ Duplicate templates
- ✅ Preview with sample data
- ✅ Variable management

### Data Sources
- ✅ Support CSV, JSON, API
- ✅ Test connections
- ✅ Preview data
- ✅ Field mapping

### Page Generator
- ✅ Select template + data source
- ✅ Bulk generate pages
- ✅ Track progress
- ✅ View generated pages

### Analytics
- ✅ View page performance
- ✅ Track page views
- ✅ SEO scores
- ✅ Top pages

---

## 🚨 Common Issues & Solutions

### Template không hiển thị
**Issue**: Variable không được thay thế
**Solution**: Kiểm tra tên variable trong template khớp với data

### Data source không kết nối
**Issue**: Error khi test data source
**Solution**:
- CSV: Kiểm tra file path đúng
- JSON: Validate JSON format
- API: Kiểm tra URL & authentication

### Pages không generate
**Issue**: "Failed to create page"
**Solution**:
- Kiểm tra template có content không
- Kiểm tra user có create posts permission
- Xem error message chi tiết

---

## 📚 Tài Liệu Chi Tiết

Xem file `EXAMPLES.md` để có:
- 4 template mẫu hoàn chỉnh
- 4 data source mẫu
- Best practices
- SEO optimization tips

---

## ✨ Next Steps

1. **Thêm Meta Tags**: Programmatic SEO > Settings
2. **Enable Analytics**: Theo dõi performance
3. **Setup Sitemap**: Tự động cập nhật
4. **Optimize Schema**: Tự động thêm structured data

---

## 💬 Support

**Problem?** Kiểm tra:
1. Template variables khớp không
2. Data source connection OK?
3. User permissions đủ?
4. PHP/WordPress version compatible?

---

## 🎓 Tiếp Theo

Sau khi generate 10-20 trang thành công:

1. **Optimize Content**
   - Add internal links
   - Improve description
   - Enhance schema markup

2. **Monitor Performance**
   - Check Analytics dashboard
   - Review page views
   - Track SEO scores

3. **Scale Up**
   - Create more templates
   - Add more data sources
   - Generate thousands of pages

---

**Happy page generation! 🚀**
