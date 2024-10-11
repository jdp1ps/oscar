#!/bin/bash
git log --pretty=format:"<div class="line"><span class="hash">%h</span><span class="author">%an</span><time>%ai</time><span class="message">%s</span></div>" > public/gitlogs.html
