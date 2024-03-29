<template>
	<div>
		<el-row>
			<FiltersFrom :types="types" :years="years" @searchSubmit="searchSubmit">
				<template>
					<slot></slot>
					<el-button @click="download">导出</el-button>
				</template>
			</FiltersFrom>
		</el-row>
		<div class="books-list-wrap">
			<el-row>
				<el-col :span="8" v-for="(book, idx) in books" :key="idx">
					<el-card shadow="hover" class="book-card">
						<div class="the-book-wrap">
							<div class="book-image">
								<figure class="image">
									<img :src="avatarUrl(book)" class="book-image" />
								</figure>
							</div>
							<div class="book-desc">
								<p class="title">
									<el-button type="text">教材名: {{ book.name }}({{ book.edition }})</el-button>
								</p>
								<p class="author">作者: {{ book.author }}</p>
								<p class="press">出版: {{ book.press }}</p>
								<p class="price">
									价格: ¥{{ book.price }}
									<span class="internal" v-if="asAdmin">(进价: ¥{{ book.purchase_price }})</span>
								</p>
								<p class="press">
									<!-- 课程: &nbsp;
                                  <el-tag size="mini" v-for="(c, idx) in book.courses" :key="idx" style="margin-right: 3px;">
                                      {{ getCourseNameText(c.course_id) }}
                                  </el-tag>
									<el-tag size="mini" v-if="!book.courses || book.courses.length === 0" type="info">未关联任何课程</el-tag>-->
								</p>
							</div>
						</div>
						<el-divider style="margin: 0;"></el-divider>
						<div>
							<!-- <el-button type="text" class="button" v-on:click="connectCoursesHandler(book)">关联/管理课程</el-button> -->
							<el-button type="text" class="button" v-on:click="editBookHandler(book)">编辑教材</el-button>
							<el-button
								style="float: right;color: red;"
								icon="el-icon-delete"
								type="text"
								class="button"
								v-on:click="deleteHandler(book)"
							>删除</el-button>
						</div>
					</el-card>
				</el-col>
			</el-row>
		</div>
	</div>
</template>

<script>
import { Util } from "../../common/utils";
import { Constants } from "../../common/constants";
import FiltersFrom from "./FiltersFrom";
import tableToExcel from "./tableToExcel";
export default {
	name: "TextbooksTable",
	components: {
		FiltersFrom
	},
	props: {
		books: {
			// 书
			type: Array,
			required: true
		},
		asAdmin: {
			type: Boolean,
			required: true
		},
		courses: {
			type: Array,
			required: true,
			default: function() {
				return [];
			}
		},
		years: {
			type: Array,
			required: true,
			default: function() {
				return [];
			}
		},
		types: {
			type: Array,
			required: true,
			default: function() {
				return [];
			}
		}
	},
	data() {
		return {
			highlightIdx: -1,
			search: {}
		};
	},
	methods: {
		bookItemClicked: function(idx, row) {
			this.highlightIdx = idx;
			this.$emit("book-item-clicked", { idx: idx, course: row });
		},
		editBookHandler: function(book) {
			this.$emit("book-edit", { book: book });
		},
		deleteHandler: async function(book) {
      // /teacher/textbook/delete
      let onlyacl =  await axios.post(
        '/teacher/textbook/delete',
        {
          onlyacl:1
        }
      )
      if(onlyacl.data != 'ok') return;

			this.$confirm("此操作将永久删除该教材, 是否继续?", "提示", {
				confirmButtonText: "确定",
				cancelButtonText: "取消",
				type: "warning"
			})
				.then(() => {
					this.$emit("book-delete", { book: book });
				})
				.catch(() => {
					this.$message({
						type: "info",
						message: "已取消删除"
					});
				});
		},
		// 去关联课程
		connectCoursesHandler: function(book) {
			this.$emit("connect-courses", { book: book });
		},
		avatarUrl: function(book) {
			if (!book || !book.medias || book.medias.length === 0) {
				return "/assets/img/mega-img1.jpg";
			} else {
				return book.medias[0].url;
			}
		},
		getCourseNameText: function(courseId) {
			const c = Util.GetItemById(courseId, this.courses);
			return Util.isEmpty(c) ? "" : c.name;
		},
		searchSubmit(e) {
			this.search = e;
			this.$emit("query-text-books", e);
		},
		async download() {
      let search = this.search;
      console.log("开始下载")
			let res = await this.$parent.loadTextbooks(1)
      let courses = res || [];
      if(!courses.length){
          this.$message({
            message: '下载失败！无数据可下载',
            type: 'warning'
          });
          return;
      }
      tableToExcel(
        [
          {
            name: "教材名称",
            formatter: item => `${item.name }`
          },
          {
            name: "教材作者",
            formatter: item => item.author
          },
          {
            name: "出版社",
            formatter: item => item.press
          },
          {
            name: "课本进价",
            formatter: item => `¥ ${item.purchase_price}`
          },
          {
            name: "课本零售价",
            formatter: item => `¥ ${item.price}`
          },
          {
            name: "年级",
            formatter: item => `${item.year_text }`
          },
          {
            name: "学期",
            formatter: item => `${item.term_text }`
          },
          {
            name: "版本",
            formatter: item => `${item.edition }`
          },
          {
            name: "类型",
            formatter: item => `${item.type_text }`
          }
        ],
        courses,
        "教材管理"
      );
		}
	}
};
</script>

<style scoped lang="scss">
$colorGrey: #c9cacc;
.books-list-wrap {
	padding: 0;
	.book-card {
		margin: 10px;
		.the-book-wrap {
			display: flex;
			.book-image {
				.image {
					.book-image {
            display: inline-block;
						margin: 0 auto;
						max-width: 190px;
						padding-right: 10px;
						height: 120px;
						border-radius: 10px;
					}
				}
			}
			.book-desc {
				p {
					margin-bottom: 4px;
				}
				.title {
					font-size: 14px;
					line-height: 20px;
					font-weight: bold;
				}
				.author,
				.press {
					font-size: 12px;
					color: $colorGrey;
				}
				.price {
					font-size: 13px;
					font-weight: bold;
					color: #f56c6c;
				}
			}
		}
	}
}
</style>
